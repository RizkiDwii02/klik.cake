<?php

namespace App\Models;

use App\Enums\CustomerOrderStatus;
use App\Enums\InternalOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_type',
        'delivery_address',
        'delivery_zone_id',
        'subtotal',
        'shipping_fee',
        'discount_amount',
        'total_amount',
        'internal_status',
        'customer_status',
        'fulfillment_date',
        'fulfillment_slot',
        'customer_notes',
        'promo_code_id',
        'expires_at',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'shipping_fee' => 'integer',
        'discount_amount' => 'integer',
        'total_amount' => 'integer',
        'fulfillment_date' => 'date',
        'expires_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('created_at', 'desc');
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Transition internal status, update customer status, and log the change.
     */
    public function updateInternalStatus(InternalOrderStatus|string $newStatus, ?string $notes = null, ?string $changedBy = 'system'): void
    {
        $statusEnum = is_string($newStatus) ? InternalOrderStatus::from($newStatus) : $newStatus;
        $fromStatus = $this->internal_status;

        $this->internal_status = $statusEnum->value;
        $this->customer_status = $statusEnum->toCustomerStatus()->value;
        $this->save();

        $this->statusLogs()->create([
            'from_status' => $fromStatus,
            'to_status' => $statusEnum->value,
            'notes' => $notes,
            'changed_by' => $changedBy,
        ]);
    }

    public function formattedTotal(): string
    {
        return 'Rp' . number_format($this->total_amount, 0, ',', '.');
    }

    public function formattedSubtotal(): string
    {
        return 'Rp' . number_format($this->subtotal, 0, ',', '.');
    }

    public function formattedShippingFee(): string
    {
        return 'Rp' . number_format($this->shipping_fee, 0, ',', '.');
    }
}
