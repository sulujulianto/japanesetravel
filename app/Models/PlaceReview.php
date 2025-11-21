<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceReview extends Model
{
    protected $fillable = [
        'place_id',
        'user_id',
        'rating',
        'comment',
    ];

    /**
     * Relasi: review milik sebuah tempat.
     */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /**
     * Relasi: review ditulis oleh seorang user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
