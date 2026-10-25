<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'fee',
        'estimated_delivery_time',
        'is_active',
    ];

    protected $casts = [
        'fee' => 'integer',
        'is_active' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function formattedFee(): string
    {
        return 'Rp' . number_format($this->fee, 0, ',', '.');
    }
}
