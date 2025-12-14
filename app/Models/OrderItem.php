<?php

namespace App\Models;

use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory, BelongsToStore;

    protected $table = 'orders_items';

    protected $fillable = [
        'store_id',
        'order_id',
        'account_id',
        'product_id',
        'item',
        'price',
        'discount',
        'vat',
        'total',
        'returned',
        'qty',
        'returned_qty',
        'is_free',
        'is_returned',
    ];

    protected $casts = [
        'price' => 'double',
        'discount' => 'double',
        'vat' => 'double',
        'total' => 'double',
        'returned' => 'double',
        'qty' => 'double',
        'returned_qty' => 'double',
        'is_free' => 'boolean',
        'is_returned' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
