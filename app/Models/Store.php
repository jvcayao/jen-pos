<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Aliziodev\LaravelTaxonomy\Models\Taxonomy;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'address',
        'phone',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Store $store) {
            if (empty($store->slug)) {
                $store->slug = Str::slug($store->name);
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function discountCodes(): HasMany
    {
        return $this->hasMany(DiscountCode::class);
    }

    public function taxonomies(): HasMany
    {
        return $this->hasMany(Taxonomy::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
