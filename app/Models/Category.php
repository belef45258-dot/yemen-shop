<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id', 'slug', 'name_ar', 'name_en',
        'description_ar', 'description_en', 'image',
        'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /* ─── Relations ─── */

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /* ─── Accessors ─── */

    public function getNameAttribute(): string
    {
        return $this->{'name_' . app()->getLocale()} ?: $this->name_en;
    }

    /* ─── Scopes ─── */

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeRoots(Builder $q): Builder
    {
        return $q->whereNull('parent_id');
    }
}
