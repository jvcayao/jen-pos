<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_to' => ['nullable', 'date'],
            'vat' => ['nullable', 'numeric', 'min:0'],
            'has_vat' => ['nullable', 'boolean'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'track_inventory' => ['nullable', 'boolean'],
            'is_activated' => ['nullable', 'boolean'],
            'has_unlimited_stock' => ['nullable', 'boolean'],
            'has_max_cart' => ['nullable', 'boolean'],
            'min_cart' => ['nullable', 'integer', 'min:1'],
            'max_cart' => ['nullable', 'integer', 'min:1'],
            'has_stock_alert' => ['nullable', 'boolean'],
            'min_stock_alert' => ['nullable', 'integer', 'min:0'],
            'max_stock_alert' => ['nullable', 'integer', 'min:0'],
            'category_id' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ];
    }
}
