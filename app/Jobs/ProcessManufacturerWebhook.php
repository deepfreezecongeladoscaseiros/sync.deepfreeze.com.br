<?php

namespace App\Jobs;

use App\Models\Manufacturer;
use App\Models\ManufacturerWebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessManufacturerWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [10, 30, 60];

    protected $manufacturerData;
    protected $logId;

    public function __construct(array $manufacturerData, int $logId)
    {
        $this->manufacturerData = $manufacturerData;
        $this->logId = $logId;
    }

    public function handle(): void
    {
        $log = ManufacturerWebhookLog::find($this->logId);

        if (!$log) {
            Log::error('ManufacturerWebhookLog not found', ['log_id' => $this->logId]);
            return;
        }

        try {
            $legacy_id = $this->manufacturerData['legacy_id'];
            $manufacturer = Manufacturer::where('legacy_id', $legacy_id)->first();
            
            if (!$manufacturer) {
                $log->update(['event_type' => 'create']);
            }

            $manufacturerPayload = [
                'legacy_id' => $legacy_id,
                'trade_name' => $this->manufacturerData['trade_name'],
                'legal_name' => $this->manufacturerData['legal_name'] ?? null,
                'cnpj' => $this->manufacturerData['cnpj'] ?? null,
                'address' => $this->manufacturerData['address'] ?? null,
                'city' => $this->manufacturerData['city'] ?? null,
                'state' => $this->manufacturerData['state'] ?? null,
                'zip_code' => $this->manufacturerData['zip_code'] ?? null,
                'phone' => $this->manufacturerData['phone'] ?? null,
                'email' => $this->manufacturerData['email'] ?? null,
                'active' => $this->manufacturerData['active'] ?? true,
            ];

            $manufacturer = Manufacturer::updateOrCreate(
                ['legacy_id' => $legacy_id],
                $manufacturerPayload
            );

            $log->update([
                'manufacturer_id' => $manufacturer->id,
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

            Log::error('ProcessManufacturerWebhook failed', [
                'log_id' => $this->logId,
                'legacy_id' => $this->manufacturerData['legacy_id'] ?? null,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $log = ManufacturerWebhookLog::find($this->logId);
        
        if ($log) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'Max retries exceeded: ' . $exception->getMessage(),
                'processed_at' => now(),
            ]);
        }

        Log::error('ProcessManufacturerWebhook permanently failed', [
            'log_id' => $this->logId,
            'error' => $exception->getMessage(),
        ]);
    }
}
