<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** @property OrderStatus $status */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_snapshot',
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
            'customer_snapshot' => 'encrypted:array',
            'shipping_address_snapshot' => 'encrypted:array',
            'status' => OrderStatus::class,
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

    public function canTransitionTo(OrderStatus|string $nextStatus): bool
    {
        $nextStatus = is_string($nextStatus) ? OrderStatus::tryFrom($nextStatus) : $nextStatus;

        return $nextStatus !== null && in_array($nextStatus, $this->status->allowedUpdates(), true);
    }

    /** @return list<string> */
    public function allowedStatusUpdates(): array
    {
        return array_map(
            static fn (OrderStatus $status): string => $status->value,
            $this->status->allowedUpdates(),
        );
    }
}
