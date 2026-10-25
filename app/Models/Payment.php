<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'provider',
        'method',
        'provider_transaction_id',
        'gross_amount',
        'status',
        'raw_notification',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'gross_amount' => 'integer',
        'raw_notification' => 'array',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::PAID->value || $this->status === 'paid';
    }

    public function formattedGrossAmount(): string
    {
        return 'Rp' . number_format($this->gross_amount, 0, ',', '.');
    }
}
