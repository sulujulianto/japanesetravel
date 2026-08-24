<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'souvenir_id',
        'quantity',
        'price',
        'product_name',
        'product_price',
        'product_image',
    ];

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<Souvenir, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Souvenir::class, 'souvenir_id');
    }

    public function getResolvedImageUrlAttribute(): ?string
    {
        return $this->product?->image_url ?? Media::url($this->product_image);
    }
}
