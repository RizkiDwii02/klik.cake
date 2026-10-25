<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'variant_id',
        'product_name',
        'variant_name',
        'price',
        'quantity',
        'greeting_card',
        'fulfillment_date',
        'fulfillment_slot',
    ];

    protected $casts = [
        'price' => 'integer',
        'quantity' => 'integer',
        'fulfillment_date' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    public function lineTotal(): int
    {
        return $this->price * $this->quantity;
    }

    public function formattedPrice(): string
    {
        return 'Rp' . number_format($this->price, 0, ',', '.');
    }

    public function formattedLineTotal(): string
    {
        return 'Rp' . number_format($this->lineTotal(), 0, ',', '.');
    }
}
