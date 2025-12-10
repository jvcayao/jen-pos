<?php

namespace App\Models;

use Binafy\LaravelCart\Cartable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Aliziodev\LaravelTaxonomy\Traits\HasTaxonomy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model implements Cartable
{
    use HasFactory, HasTaxonomy;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'price',
        'image_path',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(\Aliziodev\LaravelTaxonomy\Models\Taxonomy::class, 'category_id');
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (!$this->image_path) {
                    return null;
                }

                if (str_starts_with($this->image_path, 'http')) {
                    return $this->image_path;
                }

                return asset('storage/' . ltrim($this->image_path, '/'));
            }
        );
    }

    public function getPrice(): float
    {
        return (float) $this->price;
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, fn ($q) =>
            $q->where('name', 'like', '%' . $search . '%')
        );
    }
}
