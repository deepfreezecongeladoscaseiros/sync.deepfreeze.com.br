<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductWebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'integration_id',
        'legacy_id',
        'event_type',
        'payload',
        'headers',
        'status',
        'processed_at',
        'error_message',
        'retry_count',
        'ip_address',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
        'processed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }
}
