<?php

namespace App\Models;

use Illuminate\Support\Str;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Traits\HasWalletFloat;
use Illuminate\Database\Eloquent\Model;
use Bavix\Wallet\Interfaces\WalletFloat;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model implements Wallet, WalletFloat
{
    use HasFactory, HasWalletFloat, HasWallets;

    // Wallet slugs
    public const WALLET_SUBSCRIBE = 'subscribe';

    public const WALLET_NON_SUBSCRIBE = 'non-subscribe';

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
        'wallet_type',
        'qr_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'full_name',
        'wallet_balance',
        'subscribe_wallet_balance',
        'non_subscribe_wallet_balance',
        'qr_code_url',
    ];

    protected static function booted(): void
    {
        static::creating(function (Student $student) {
            if (empty($student->qr_token)) {
                $student->qr_token = (string) Str::uuid();
            }
        });

        static::updating(function (Student $student) {
            if (empty($student->qr_token)) {
                $student->qr_token = (string) Str::uuid();
            }
        });
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the default wallet balance (for backward compatibility)
     */
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

    /**
     * Get the subscribe wallet balance
     */
    public function getSubscribeWalletBalanceAttribute(): float
    {
        try {
            $wallet = $this->getWallet(self::WALLET_SUBSCRIBE);

            return $wallet ? (float) $wallet->balanceFloatNum : 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Get the non-subscribe wallet balance
     */
    public function getNonSubscribeWalletBalanceAttribute(): float
    {
        try {
            $wallet = $this->getWallet(self::WALLET_NON_SUBSCRIBE);

            return $wallet ? (float) $wallet->balanceFloatNum : 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Check if student has a subscribe wallet
     */
    public function hasSubscribeWallet(): bool
    {
        return $this->hasWallet(self::WALLET_SUBSCRIBE);
    }

    /**
     * Check if student has a non-subscribe wallet
     */
    public function hasNonSubscribeWallet(): bool
    {
        return $this->hasWallet(self::WALLET_NON_SUBSCRIBE);
    }

    /**
     * Get the subscribe wallet
     */
    public function getSubscribeWallet()
    {
        return $this->getWallet(self::WALLET_SUBSCRIBE);
    }

    /**
     * Get the non-subscribe wallet
     */
    public function getNonSubscribeWallet()
    {
        return $this->getWallet(self::WALLET_NON_SUBSCRIBE);
    }

    /**
     * Create the subscribe wallet if it doesn't exist
     */
    public function createSubscribeWallet()
    {
        if (!$this->hasSubscribeWallet()) {
            return $this->createWallet([
                'name' => 'Subscribe Wallet',
                'slug' => self::WALLET_SUBSCRIBE,
                'description' => 'Subscription-based wallet for regular meals',
            ]);
        }

        return $this->getSubscribeWallet();
    }

    /**
     * Create the non-subscribe wallet if it doesn't exist
     */
    public function createNonSubscribeWallet()
    {
        if (!$this->hasNonSubscribeWallet()) {
            return $this->createWallet([
                'name' => 'Non-Subscribe Wallet',
                'slug' => self::WALLET_NON_SUBSCRIBE,
                'description' => 'Regular wallet for non-subscription purchases',
            ]);
        }

        return $this->getNonSubscribeWallet();
    }

    /**
     * Check if student has any wallet with balance
     */
    public function hasAnyWalletBalance(): bool
    {
        return $this->subscribe_wallet_balance > 0 || $this->non_subscribe_wallet_balance > 0;
    }

    /**
     * Get total balance across all wallets
     */
    public function getTotalWalletBalanceAttribute(): float
    {
        return $this->subscribe_wallet_balance + $this->non_subscribe_wallet_balance;
    }

    /**
     * Get the student's assigned wallet based on wallet_type
     */
    public function getAssignedWallet()
    {
        if (!$this->wallet_type) {
            return null;
        }

        return $this->wallet_type === self::WALLET_SUBSCRIBE
            ? $this->getSubscribeWallet()
            : $this->getNonSubscribeWallet();
    }

    /**
     * Get the student's assigned wallet balance
     */
    public function getAssignedWalletBalanceAttribute(): float
    {
        if (!$this->wallet_type) {
            return 0.0;
        }

        return $this->wallet_type === self::WALLET_SUBSCRIBE
            ? $this->subscribe_wallet_balance
            : $this->non_subscribe_wallet_balance;
    }

    /**
     * Check if student has an assigned wallet
     */
    public function hasAssignedWallet(): bool
    {
        if (!$this->wallet_type) {
            return false;
        }

        return $this->wallet_type === self::WALLET_SUBSCRIBE
            ? $this->hasSubscribeWallet()
            : $this->hasNonSubscribeWallet();
    }

    /**
     * Create the assigned wallet based on wallet_type
     */
    public function createAssignedWallet()
    {
        if (!$this->wallet_type) {
            return null;
        }

        return $this->wallet_type === self::WALLET_SUBSCRIBE
            ? $this->createSubscribeWallet()
            : $this->createNonSubscribeWallet();
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

    public function getQrPayload(): string
    {
        return 'student:'.$this->qr_token;
    }

    public function getQrCodeUrlAttribute(): string
    {
        return route('students.qr-code', $this);
    }
}
