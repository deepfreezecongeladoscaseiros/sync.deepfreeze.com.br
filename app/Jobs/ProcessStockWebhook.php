<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductStockWebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessStockWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [10, 30, 60];

    protected $stockData;
    protected $logId;

    public function __construct(array $stockData, int $logId)
    {
        $this->stockData = $stockData;
        $this->logId = $logId;
    }

    public function handle(): void
    {
        $log = ProductStockWebhookLog::find($this->logId);

        if (!$log) {
            Log::error('ProductStockWebhookLog not found', ['log_id' => $this->logId]);
            return;
        }

        try {
            $legacy_id = $this->stockData['legacy_id'];
            $product = Product::where('legacy_id', $legacy_id)->first();

            if (!$product) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => 'Product not found. Use product-update webhook to create it first.',
                    'processed_at' => now(),
                ]);
                return;
            }

            $product->update(['stock' => $this->stockData['stock']]);

            $log->update([
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

            Log::error('ProcessStockWebhook failed', [
                'log_id' => $this->logId,
                'legacy_id' => $this->stockData['legacy_id'] ?? null,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $log = ProductStockWebhookLog::find($this->logId);
        
        if ($log) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'Max retries exceeded: ' . $exception->getMessage(),
                'processed_at' => now(),
            ]);
        }

        Log::error('ProcessStockWebhook permanently failed', [
            'log_id' => $this->logId,
            'error' => $exception->getMessage(),
        ]);
    }
}
