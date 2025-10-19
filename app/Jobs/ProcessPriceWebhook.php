<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductPriceWebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPriceWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [10, 30, 60];

    protected $priceData;
    protected $logId;

    public function __construct(array $priceData, int $logId)
    {
        $this->priceData = $priceData;
        $this->logId = $logId;
    }

    public function handle(): void
    {
        $log = ProductPriceWebhookLog::find($this->logId);

        if (!$log) {
            Log::error('ProductPriceWebhookLog not found', ['log_id' => $this->logId]);
            return;
        }

        try {
            $legacy_id = $this->priceData['legacy_id'];
            $product = Product::where('legacy_id', $legacy_id)->first();

            if (!$product) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => 'Product not found. Use product-update webhook to create it first.',
                    'processed_at' => now(),
                ]);
                return;
            }

            $product->update([
                'price' => $this->priceData['preco'],
                'promotional_price' => $this->priceData['preco_promocional'] ?? null,
            ]);

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

            Log::error('ProcessPriceWebhook failed', [
                'log_id' => $this->logId,
                'legacy_id' => $this->priceData['legacy_id'] ?? null,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $log = ProductPriceWebhookLog::find($this->logId);
        
        if ($log) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'Max retries exceeded: ' . $exception->getMessage(),
                'processed_at' => now(),
            ]);
        }

        Log::error('ProcessPriceWebhook permanently failed', [
            'log_id' => $this->logId,
            'error' => $exception->getMessage(),
        ]);
    }
}
