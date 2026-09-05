<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    public const TYPE_ORDER_RESERVATION = 'order_reservation';

    public const TYPE_ORDER_RESTORATION = 'order_restoration';

    public const TYPE_ADMIN_RESTOCK = 'admin_restock';

    public const TYPE_ADMIN_DEDUCTION = 'admin_deduction';

    public const TYPE_INITIAL_STOCK = 'initial_stock';

    public const TYPE_ADMIN_CORRECTION = 'admin_correction';

    public const UPDATED_AT = null;

    protected $fillable = [
        'souvenir_id',
        'order_id',
        'actor_id',
        'type',
        'quantity_delta',
        'stock_before',
        'stock_after',
        'reference',
        'product_name_snapshot',
        'actor_name_snapshot',
        'metadata',
    ];

    protected $casts = [
        'quantity_delta' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'product_name_snapshot' => 'array',
        'metadata' => 'array',
    ];

    /** @return BelongsTo<Souvenir, $this> */
    public function souvenir(): BelongsTo
    {
        return $this->belongsTo(Souvenir::class);
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
