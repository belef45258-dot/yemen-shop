<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'method', 'status', 'amount', 'currency',
        'transaction_id', 'reference_number', 'payer_phone',
        'receiver_wallet', 'screenshot', 'provider_response',
        'notes', 'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'provider_response' => 'array',
        'paid_at' => 'datetime',
    ];

    public const METHODS = [
        'jaib' => 'محفظة جيب',
        'jawali' => 'محفظة جوالي',
        'cash' => 'محفظة كاش',
        'one_cash' => 'ون كاش',
        'kuraimi' => 'حاسب - بنك الكريمي',
        'whatsapp' => 'واتساب',
        'stripe' => 'بطاقة ائتمانية',
        'cod' => 'الدفع عند الاستلام',
    ];

    public const STATUSES = [
        'pending' => 'قيد الانتظار',
        'processing' => 'قيد المعالجة',
        'completed' => 'مكتمل',
        'failed' => 'فشل',
        'refunded' => 'مُرجَع',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getScreenshotUrlAttribute(): ?string
    {
        return $this->screenshot ? asset('storage/' . $this->screenshot) : null;
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed', 'paid_at' => now()]);
    }

    public function markAsFailed(?string $reason = null): void
    {
        $this->update(['status' => 'failed', 'notes' => $reason]);
    }
}
