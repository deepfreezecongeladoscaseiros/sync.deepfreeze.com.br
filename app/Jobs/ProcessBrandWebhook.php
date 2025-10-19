<?php

namespace App\Jobs;

use App\Models\Brand;
use App\Models\BrandWebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBrandWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [10, 30, 60];

    protected $brandData;
    protected $logId;

    public function __construct(array $brandData, int $logId)
    {
        $this->brandData = $brandData;
        $this->logId = $logId;
    }

    public function handle(): void
    {
        $log = BrandWebhookLog::find($this->logId);

        if (!$log) {
            Log::error('BrandWebhookLog not found', ['log_id' => $this->logId]);
            return;
        }

        try {
            $legacy_id = $this->brandData['legacy_id'];
            $brand = Brand::where('legacy_id', $legacy_id)->first();
            
            if (!$brand) {
                $log->update(['event_type' => 'create']);
            }

            $brandPayload = [
                'legacy_id' => $legacy_id,
                'brand' => $this->brandData['brand'],
                'slug' => $this->brandData['slug'] ?? \Illuminate\Support\Str::slug($this->brandData['brand']),
            ];

            $brand = Brand::updateOrCreate(
                ['legacy_id' => $legacy_id],
                $brandPayload
            );

            $log->update([
                'brand_id' => $brand->id,
                'status' => 'success',
                'processed_at' => now(),
            ]);

        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'retry_count' => $log->retry_count + 1,
                'processed_at' => now(),
            ]);

            Log::error('ProcessBrandWebhook failed', [
                'log_id' => $this->logId,
                'legacy_id' => $this->brandData['legacy_id'] ?? null,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $log = BrandWebhookLog::find($this->logId);
        
        if ($log) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'Max retries exceeded: ' . $exception->getMessage(),
                'processed_at' => now(),
            ]);
        }

        Log::error('ProcessBrandWebhook permanently failed', [
            'log_id' => $this->logId,
            'error' => $exception->getMessage(),
        ]);
    }
}
