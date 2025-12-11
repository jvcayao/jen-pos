<?php

namespace App\Models;

use Binafy\LaravelCart\Cartable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Aliziodev\LaravelTaxonomy\Traits\HasTaxonomy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model implements Cartable
{
    use HasFactory, HasTaxonomy;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'sku',
        'barcode',
        'price',
        'discount',
        'discount_to',
        'vat',
        'has_vat',
        'stock',
        'track_inventory',
        'is_activated',
        'has_unlimited_stock',
        'has_max_cart',
        'min_cart',
        'max_cart',
        'has_stock_alert',
        'min_stock_alert',
        'max_stock_alert',
        'image_path',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_to' => 'datetime',
        'vat' => 'decimal:2',
        'has_vat' => 'boolean',
        'stock' => 'integer',
        'track_inventory' => 'boolean',
        'is_activated' => 'boolean',
        'has_unlimited_stock' => 'boolean',
        'has_max_cart' => 'boolean',
        'min_cart' => 'integer',
        'max_cart' => 'integer',
        'has_stock_alert' => 'boolean',
        'min_stock_alert' => 'integer',
        'max_stock_alert' => 'integer',
    ];

    public function isInStock(int $quantity = 1): bool
    {
        if (!$this->track_inventory) {
            return true;
        }

        return $this->stock >= $quantity;
    }

    public function decrementStock(int $quantity = 1): void
    {
        if ($this->track_inventory) {
            $this->decrement('stock', $quantity);
        }
    }

    public function incrementStock(int $quantity = 1): void
    {
        if ($this->track_inventory) {
            $this->increment('stock', $quantity);
        }
    }

    public function scopeByBarcode(Builder $query, string $barcode): Builder
    {
        return $query->where('barcode', $barcode);
    }

    public function scopeBySku(Builder $query, string $sku): Builder
    {
        return $query->where('sku', $sku);
    }

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

                return asset('storage/'.ltrim($this->image_path, '/'));
            }
        );
    }

    public function getPrice(): float
    {
        return (float) $this->price;
    }

    /**
     * Get VAT rate from config
     */
    public static function getVatRate(): float
    {
        return (float) config('payment.tax_rate', 0.12);
    }

    /**
     * Check if product has VAT
     */
    public function hasVat(): bool
    {
        return (bool) ($this->has_vat ?? true);
    }

    /**
     * Get price with VAT applied (if product has VAT)
     */
    public function getPriceWithVat(): float
    {
        $basePrice = $this->getPrice();

        if (!$this->hasVat()) {
            return $basePrice;
        }

        return $basePrice * (1 + self::getVatRate());
    }

    /**
     * Get VAT amount for this product
     */
    public function getVatAmount(): float
    {
        if (!$this->hasVat()) {
            return 0;
        }

        return $this->getPrice() * self::getVatRate();
    }

    /**
     * Get final price with discount and VAT applied
     */
    public function getFinalPrice(): float
    {
        $basePrice = $this->getPrice();

        // Apply discount if active
        if ($this->discount > 0) {
            $discountValid = !$this->discount_to || $this->discount_to->isFuture();
            if ($discountValid) {
                $basePrice = $basePrice * (1 - ($this->discount / 100));
            }
        }

        // Apply VAT only if product has VAT
        if ($this->hasVat()) {
            return $basePrice * (1 + self::getVatRate());
        }

        return $basePrice;
    }

    /**
     * Generate SKU based on category initials + sequential number
     */
    public static function generateSku(?string $categoryName = null): string
    {
        $prefix = 'PRD';

        if ($categoryName) {
            // Get initials from category name (e.g., "Fried Chicken" => "FC")
            $words = explode(' ', trim($categoryName));
            $initials = '';
            foreach ($words as $word) {
                if (!empty($word)) {
                    $initials .= strtoupper(substr($word, 0, 1));
                }
            }
            $prefix = $initials ?: 'PRD';
        }

        // Get next sequential number
        $lastProduct = self::where('sku', 'like', $prefix.'-%')
            ->orderByRaw('CAST(SUBSTRING(sku, LENGTH(?) + 2) AS UNSIGNED) DESC', [$prefix])
            ->first();

        if ($lastProduct && preg_match('/-(\d+)$/', $lastProduct->sku, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix.'-'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique barcode using UUID format
     */
    public static function generateBarcode(): string
    {
        return (string) \Illuminate\Support\Str::uuid();
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, fn ($q) => $q->where('name', 'like', '%'.$search.'%')
        );
    }
}
