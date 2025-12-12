<?php

namespace App\Models;

use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Interfaces\WalletFloat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model implements Wallet, WalletFloat
{
    use HasFactory, HasWalletFloat;

    protected $fillable = [
        'student_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'grade_level',
        'section',
        'guardian_name',
        'guardian_phone',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'full_name',
        'wallet_balance',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getWalletBalanceAttribute(): float
    {
        // Check if wallet relationship exists to avoid creating wallet on every access
        if ($this->relationLoaded('wallet') && $this->wallet === null) {
            return 0.0;
        }

        try {
            return (float) $this->balanceFloatNum;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('student_id', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }
}
