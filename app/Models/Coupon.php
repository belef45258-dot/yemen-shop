<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'description_ar', 'description_en', 'type', 'value',
        'min_order_amount', 'max_discount', 'usage_limit',
        'usage_limit_per_user', 'used_count', 'starts_at',
        'expires_at', 'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function getIsValidAttribute(): bool
    {
        if (! $this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if (! $this->is_valid || $subtotal < (float) $this->min_order_amount) {
            return 0.0;
        }

        if ($this->type === 'fixed') {
            return min((float) $this->value, $subtotal);
        }

        $discount = $subtotal * ((float) $this->value / 100);

        if ($this->max_discount) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round($discount, 2);
    }

    public function scopeValid(Builder $q): Builder
    {
        return $q->where('is_active', true)
                 ->where(fn($qq) => $qq->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                 ->where(fn($qq) => $qq->whereNull('starts_at')->orWhere('starts_at', '<=', now()));
    }
}
