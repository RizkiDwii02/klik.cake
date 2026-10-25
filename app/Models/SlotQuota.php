<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlotQuota extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'time_slot',
        'capacity',
        'booked',
    ];

    protected $casts = [
        'date' => 'date',
        'capacity' => 'integer',
        'booked' => 'integer',
    ];

    public function remainingCapacity(): int
    {
        return max(0, $this->capacity - $this->booked);
    }

    public function isAvailable(int $quantity = 1): bool
    {
        return ($this->booked + $quantity) <= $this->capacity;
    }
}
