<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'google_id', 'avatar',
        'phone', 'role', 'locale', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token', 'google_id'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function addresses(): HasMany { return $this->hasMany(Address::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class)->latest(); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }
    public function carts(): HasMany { return $this->hasMany(Cart::class); }

    public function activeCart(): HasOne
    {
        return $this->hasOne(Cart::class)->latestOfMany();
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isCustomer(): bool { return $this->role === 'customer'; }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return str_starts_with($this->avatar, 'http')
                ? $this->avatar
                : asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
    }

    public function getTotalSpentAttribute(): float
    {
        return (float) $this->orders()->where('payment_status', 'paid')->sum('total');
    }
}
