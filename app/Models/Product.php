<?php

namespace App\Models;

use App\Enums\AvailabilityType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'collection_id',
        'name',
        'slug',
        'description',
        'portion_info',
        'ingredients',
        'allergens',
        'shelf_life',
        'lead_time_days',
        'availability_type',
        'is_featured',
        'is_fresh_bake',
        'rating',
    ];

    protected $casts = [
        'lead_time_days' => 'integer',
        'is_featured' => 'boolean',
        'is_fresh_bake' => 'boolean',
        'rating' => 'float',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function minPrice(): int
    {
        return (int) ($this->variants->min('price') ?? $this->variants()->min('price') ?? 0);
    }

    public function maxPrice(): int
    {
        return (int) ($this->variants->max('price') ?? $this->variants()->max('price') ?? 0);
    }

    public function formattedPrice(): string
    {
        return 'Rp' . number_format($this->minPrice(), 0, ',', '.');
    }

    public function formattedPriceRange(): string
    {
        $min = $this->minPrice();
        $max = $this->maxPrice();

        if ($min === $max || $max === 0) {
            return $this->formattedPrice();
        }

        return 'Rp' . number_format($min, 0, ',', '.') . ' – Rp' . number_format($max, 0, ',', '.');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeFreshBake($query)
    {
        return $query->where('is_fresh_bake', true);
    }
}
