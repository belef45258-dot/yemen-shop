<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'coupon_code', 'status', 'payment_status',
        'subtotal', 'discount', 'shipping_cost', 'tax', 'total', 'currency',
        'shipping_address', 'customer_notes', 'admin_notes',
        'paid_at', 'shipped_at', 'delivered_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_address' => 'array',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /* ─── Constants ─── */

    public const STATUSES = [
        'pending' => 'قيد الانتظار',
        'processing' => 'قيد المعالجة',
        'shipped' => 'تم الشحن',
        'delivered' => 'تم التسليم',
        'cancelled' => 'ملغي',
        'refunded' => 'مُرجَع',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid' => 'غير مدفوع',
        'paid' => 'مدفوع',
        'failed' => 'فشل',
        'refunded' => 'مُرجَع',
    ];

    /* ─── Relations ─── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /* ─── Accessors ─── */

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status;
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function getCanBeCancelledAttribute(): bool
    {
        return in_array($this->status, ['pending', 'processing'], true);
    }

    /* ─── Scopes ─── */

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', 'pending');
    }

    public function scopePaid(Builder $q): Builder
    {
        return $q->where('payment_status', 'paid');
    }

    public function scopeForUser(Builder $q, int $userId): Builder
    {
        return $q->where('user_id', $userId);
    }

    /* ─── Status Methods ─── */

    public function markAsPaid(?string $transactionId = null): void
    {
        $this->update([
            'payment_status' => 'paid',
            'status' => 'processing',
            'paid_at' => now(),
        ]);
    }

    public function markAsShipped(): void
    {
        $this->update(['status' => 'shipped', 'shipped_at' => now()]);
    }

    public function markAsDelivered(): void
    {
        $this->update(['status' => 'delivered', 'delivered_at' => now()]);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }
}
