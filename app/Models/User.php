<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'store_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * @deprecated Use stores() instead for multi-store access
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class)->withTimestamps();
    }

    public function isHeadOfficeAdmin(): bool
    {
        return $this->hasRole('head-office-admin');
    }

    public function isStoreAdmin(): bool
    {
        return $this->hasRole('store-admin');
    }

    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }

    public function canAccessAllStores(): bool
    {
        return $this->isHeadOfficeAdmin();
    }

    public function canAccessStore(int $storeId): bool
    {
        if ($this->canAccessAllStores()) {
            return true;
        }

        return $this->stores()->where('stores.id', $storeId)->exists();
    }

    public function getAccessibleStores()
    {
        if ($this->canAccessAllStores()) {
            return Store::active()->get();
        }

        return $this->stores()->where('is_active', true)->get();
    }

    public function hasMultipleStores(): bool
    {
        if ($this->canAccessAllStores()) {
            return Store::active()->count() > 1;
        }

        return $this->stores()->where('is_active', true)->count() > 1;
    }

    public function needsStoreSelection(): bool
    {
        return $this->hasMultipleStores() && !session()->has('current_store_id');
    }
}
