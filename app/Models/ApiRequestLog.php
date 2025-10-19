<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiRequestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_id',
        'method',
        'endpoint',
        'full_url',
        'query_params',
        'request_body',
        'headers',
        'ip_address',
        'user_agent',
        'status_code',
        'response_body',
        'response_time_ms',
        'error_message',
        'error_type',
    ];

    protected $casts = [
        'query_params' => 'array',
        'request_body' => 'array',
        'headers' => 'array',
        'response_body' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeErrors($query)
    {
        return $query->where('status_code', '>=', 400);
    }

    public function scopeSlowRequests($query, $thresholdMs = 1000)
    {
        return $query->where('response_time_ms', '>', $thresholdMs);
    }

    public function scopeByEndpoint($query, $endpoint)
    {
        return $query->where('endpoint', 'like', "%{$endpoint}%");
    }

    public function scopeByIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    public function isError()
    {
        return $this->status_code >= 400;
    }

    public function isSlow($thresholdMs = 1000)
    {
        return $this->response_time_ms > $thresholdMs;
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->status_code >= 500) {
            return 'danger';
        } elseif ($this->status_code >= 400) {
            return 'warning';
        } elseif ($this->status_code >= 300) {
            return 'info';
        } else {
            return 'success';
        }
    }

    public function getResponseTimeColorAttribute()
    {
        if ($this->response_time_ms > 2000) {
            return 'danger';
        } elseif ($this->response_time_ms > 1000) {
            return 'warning';
        } else {
            return 'success';
        }
    }

    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }
}
