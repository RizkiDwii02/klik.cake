<?php

namespace App\Models;

use App\Enums\PromoCodeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'integer',
        'min_order_amount' => 'integer',
        'max_discount' => 'integer',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isValid(int $subtotal = 0): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($subtotal < $this->min_order_amount) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(int $subtotal): int
    {
        if (! $this->isValid($subtotal)) {
            return 0;
        }

        if ($this->type === PromoCodeType::FIXED->value || $this->type === 'fixed') {
            return (int) min($this->value, $subtotal);
        }

        // Percentage discount
        $discount = (int) round(($subtotal * $this->value) / 100);

        if ($this->max_discount !== null && $this->max_discount > 0) {
            $discount = min($discount, $this->max_discount);
        }

        return (int) min($discount, $subtotal);
    }
}
