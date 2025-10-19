<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrayCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'api_host',
        'consumer_key',
        'consumer_secret',
        'code',
        'access_token',
        'refresh_token',
        'date_expiration_access_token',
        'date_expiration_refresh_token',
        'date_activated',
    ];
}
