<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\CategoryWebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCategoryWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [10, 30, 60];

    protected $categoryData;
    protected $logId;

    public function __construct(array $categoryData, int $logId)
    {
        $this->categoryData = $categoryData;
        $this->logId = $logId;
    }

    public function handle(): void
    {
        $log = CategoryWebhookLog::find($this->logId);

        if (!$log) {
            Log::error('CategoryWebhookLog not found', ['log_id' => $this->logId]);
            return;
        }

        try {
            $legacy_id = $this->categoryData['legacy_id'];
            $category = Category::where('legacy_id', $legacy_id)->first();
            
            if (!$category) {
                $log->update(['event_type' => 'create']);
            }

            $categoryPayload = [
                'legacy_id' => $legacy_id,
                'name' => $this->categoryData['name'],
                'slug' => $this->categoryData['slug'] ?? \Illuminate\Support\Str::slug($this->categoryData['name']),
                'description' => $this->categoryData['description'] ?? null,
            ];

            $category = Category::updateOrCreate(
                ['legacy_id' => $legacy_id],
                $categoryPayload
            );

            $log->update([
                'category_id' => $category->id,
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

            Log::error('ProcessCategoryWebhook failed', [
                'log_id' => $this->logId,
                'legacy_id' => $this->categoryData['legacy_id'] ?? null,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $log = CategoryWebhookLog::find($this->logId);
        
        if ($log) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'Max retries exceeded: ' . $exception->getMessage(),
                'processed_at' => now(),
            ]);
        }

        Log::error('ProcessCategoryWebhook permanently failed', [
            'log_id' => $this->logId,
            'error' => $exception->getMessage(),
        ]);
    }
}
