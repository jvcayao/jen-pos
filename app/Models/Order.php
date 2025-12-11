<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'type',
        'user_id',
        'account_id',
        'cashier_id',
        'source',
        'total',
        'discount',
        'shipping',
        'vat',
        'status',
        'notes',
        'is_void',
        'return_total',
        'reason',
        'is_payed',
        'payment_method',
        'payment_vendor',
        'payment_vendor_id',
    ];

    protected $casts = [
        'total' => 'double',
        'discount' => 'double',
        'shipping' => 'double',
        'vat' => 'double',
        'return_total' => 'double',
        'is_void' => 'boolean',
        'is_payed' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
