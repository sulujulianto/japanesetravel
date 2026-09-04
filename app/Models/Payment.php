<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /** @var array<string, list<string>> */
    private const ALLOWED_STATUS_TRANSITIONS = [
        'pending' => ['paid', 'failed', 'expired', 'refunded'],
        'failed' => ['paid'],
        'expired' => ['paid'],
        'paid' => ['refunded'],
        'refunded' => [],
    ];

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

    public function canTransitionTo(string $nextStatus): bool
    {
        if ($nextStatus === $this->status) {
            return false;
        }

        return in_array($nextStatus, self::ALLOWED_STATUS_TRANSITIONS[(string) $this->status] ?? [], true);
    }
}
