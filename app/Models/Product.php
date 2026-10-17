<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasSlug;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'compare_at_price',
        'is_active',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * The image flagged as primary, falling back to the first one by sort order
     * so a product with images always has something to show.
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function primaryImageUrl(): string
    {
        return $this->primaryImage?->url ?? asset('images/placeholder.svg');
    }

    /**
     * True when compare_at_price is set and higher than the selling price.
     */
    protected function onSale(): Attribute
    {
        return Attribute::get(fn (): bool => $this->compare_at_price !== null
            && (float) $this->compare_at_price > (float) $this->price);
    }

    protected function discountPercentage(): Attribute
    {
        return Attribute::get(function (): ?int {
            if (! $this->on_sale) {
                return null;
            }

            $compare = (float) $this->compare_at_price;

            return (int) round((($compare - (float) $this->price) / $compare) * 100);
        });
    }

    public function totalStock(): int
    {
        return (int) $this->variants()->sum('stock_quantity');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }
}
