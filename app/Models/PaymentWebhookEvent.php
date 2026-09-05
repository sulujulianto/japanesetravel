<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use App\Enums\PaymentWebhookStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property PaymentProvider $provider
 * @property PaymentWebhookStatus $status
 */
class PaymentWebhookEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'provider',
        'event_id',
        'status',
        'payload_json',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'provider' => PaymentProvider::class,
        'status' => PaymentWebhookStatus::class,
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
