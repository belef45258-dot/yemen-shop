<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'user_id', 'order_id', 'rating',
        'title', 'comment', 'is_verified_purchase', 'is_approved',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeApproved(Builder $q): Builder
    {
        return $q->where('is_approved', true);
    }

    public function approve(): void
    {
        $this->update(['is_approved' => true]);
    }
}
