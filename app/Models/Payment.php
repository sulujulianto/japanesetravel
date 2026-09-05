<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property PaymentProvider $provider
 * @property PaymentStatus $status
 */
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
        'provider' => PaymentProvider::class,
        'status' => PaymentStatus::class,
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function webhookEvents()
    {
        return $this->hasMany(PaymentWebhookEvent::class);
    }

    public function canTransitionTo(PaymentStatus|string $nextStatus): bool
    {
        $nextStatus = is_string($nextStatus) ? PaymentStatus::tryFrom($nextStatus) : $nextStatus;
        if ($nextStatus === null || $nextStatus === $this->status) {
            return false;
        }

        return in_array($nextStatus, $this->status->allowedTransitions(), true);
    }
}
