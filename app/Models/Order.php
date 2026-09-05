<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    /**
     * @var array<string, array<int, string>>
     */
    private const ALLOWED_STATUS_TRANSITIONS = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    protected $fillable = [
        'user_id',
        'checkout_idempotency_key',
        'shipping_address_id',
        'shipping_address_snapshot',
        'stock_restored_at',
        'total_price',
        'status',
        'note',
        'admin_note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'shipping_address_snapshot' => 'encrypted:array',
            'stock_restored_at' => 'datetime',
        ];
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<UserAddress, $this> */
    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'shipping_address_id');
    }

    /** @return HasOne<Payment, $this> */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @return HasMany<InventoryMovement, $this> */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function canTransitionTo(string $nextStatus): bool
    {
        return in_array($nextStatus, $this->allowedStatusUpdates(), true);
    }

    /** @return list<string> */
    public function allowedStatusUpdates(): array
    {
        $currentStatus = (string) $this->status;

        return array_values(array_unique([
            $currentStatus,
            ...(self::ALLOWED_STATUS_TRANSITIONS[$currentStatus] ?? []),
        ]));
    }
}
