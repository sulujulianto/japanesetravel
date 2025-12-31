<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Place extends Model
{
    use HasFactory, HasTranslations;

    // Kolom mana saja yang boleh diisi secara massal
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'video_url',
        'gallery',
        'address',
        'facilities',
        'open_days',
        'open_hours',
        'opening_hours',
        'created_by',
    ];

    // Ubah data JSON/Array menjadi tipe data PHP otomatis
    protected $casts = [
        'opening_hours' => 'array',
    ];

    public $translatable = [
        'name',
        'description',
    ];

    /**
     * Relasi: Place memiliki banyak ulasan.
     */
    public function reviews()
    {
        return $this->hasMany(PlaceReview::class);
    }

    // Relasi: Setiap Place "milik" satu User (author)
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
