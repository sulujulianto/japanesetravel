<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $fillable = ['user_id', 'total_price', 'status', 'note', 'admin_note'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function canTransitionTo(string $nextStatus): bool
    {
        $currentStatus = (string) $this->status;
        if ($currentStatus === $nextStatus) {
            return true;
        }

        $allowedNextStatuses = self::ALLOWED_STATUS_TRANSITIONS[$currentStatus] ?? [];

        return in_array($nextStatus, $allowedNextStatuses, true);
    }
}
