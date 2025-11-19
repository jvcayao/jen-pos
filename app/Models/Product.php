<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Aliziodev\LaravelTaxonomy\Traits\HasTaxonomy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
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
        // Category is a taxonomy term (from aliziodev/laravel-taxonomy)
        return $this->belongsTo(\Aliziodev\LaravelTaxonomy\Models\Taxonomy::class, 'category_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        return asset('storage/'.ltrim($this->image_path, '/'));
    }
}
