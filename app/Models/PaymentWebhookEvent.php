<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
