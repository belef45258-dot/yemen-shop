<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'label', 'full_name', 'phone', 'governorate',
        'city', 'street', 'building', 'notes', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute(): string
    {
        return trim(implode('، ', array_filter([
            $this->governorate,
            $this->city,
            $this->street,
            $this->building,
        ])));
    }
}
