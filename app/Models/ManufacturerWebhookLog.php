<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufacturerWebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'manufacturer_id',
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

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }
}
