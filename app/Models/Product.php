<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'sku', 'slug', 'name_ar', 'name_en',
        'description_ar', 'description_en', 'price', 'sale_price',
        'currency', 'stock', 'low_stock_alert', 'weight',
        'is_active', 'is_featured', 'meta',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'meta' => 'array',
    ];

    /* ─── Relations ─── */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    /* ─── Accessors ─── */

    public function getNameAttribute(): string
    {
        return $this->{'name_' . app()->getLocale()} ?: $this->name_en;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->{'description_' . app()->getLocale()} ?: $this->description_en;
    }

    public function getFinalPriceAttribute(): float
    {
        return (float) ($this->sale_price ?? $this->price);
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && (float) $this->sale_price < (float) $this->price;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if (! $this->is_on_sale || (float) $this->price === 0.0) {
            return 0;
        }
        return (int) round((1 - ((float) $this->sale_price / (float) $this->price)) * 100);
    }

    public function getIsInStockAttribute(): bool
    {
        return $this->stock > 0;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock > 0 && $this->stock <= $this->low_stock_alert;
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->approvedReviews()->avg('rating'), 1);
    }

    /* ─── Scopes ─── */

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeInStock(Builder $q): Builder
    {
        return $q->where('stock', '>', 0);
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('is_featured', true);
    }

    public function scopeSearch(Builder $q, string $term): Builder
    {
        return $q->where(function ($qq) use ($term) {
            $qq->where('name_ar', 'like', "%{$term}%")
               ->orWhere('name_en', 'like', "%{$term}%")
               ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    public function scopeFilter(Builder $q, array $f): Builder
    {
        return $q
            ->when(!empty($f['category_id']), fn($qq, $_) => $qq->where('category_id', $f['category_id']))
            ->when(!empty($f['min_price']),   fn($qq, $_) => $qq->where('price', '>=', $f['min_price']))
            ->when(!empty($f['max_price']),   fn($qq, $_) => $qq->where('price', '<=', $f['max_price']))
            ->when(!empty($f['in_stock']),    fn($qq, $_) => $qq->inStock())
            ->when(!empty($f['featured']),    fn($qq, $_) => $qq->featured())
            ->when(!empty($f['search']),      fn($qq, $_) => $qq->search($f['search']));
    }

    /* ─── Stock Helpers ─── */

    public function hasStock(int $qty): bool
    {
        return $this->stock >= $qty;
    }

    public function decreaseStock(int $qty): void
    {
        $this->decrement('stock', $qty);
    }

    public function increaseStock(int $qty): void
    {
        $this->increment('stock', $qty);
    }
}
