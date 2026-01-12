<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'provider',
        'provider_ref',
        'status',
        'amount',
        'currency',
        'payload_json',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payload_json' => 'array',
        'paid_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function webhookEvents()
    {
        return $this->hasMany(PaymentWebhookEvent::class);
    }
}
