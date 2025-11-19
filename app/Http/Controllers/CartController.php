<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Http\Request;
use Binafy\LaravelCart\Models\Cart;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cartModel = Cart::query()->firstOrCreate(['user_id' => auth()->id()]);
        $cart = [
            'items' => [],
            'total' => $cartModel->calculatedPriceByQuantity(),
            'count' => $cartModel->items()->count(),
        ];
        foreach ($cartModel->items as $item) {
            $cart['items'][] = [
                'id' => $item->id,
                'name' => $item->itemable->name ?? '',
                'price' => $item->itemable->price ?? 0,
                'qty' => $item->quantity,
            ];
        }

        return Inertia::render('menu/index', ['cart' => $cart]);
    }

    public function remove(Request $request)
    {
        $qty = $request->input('qty');
        $id = $request->input('id');

        $product = Product::findOrFail($id);

        $cart = Cart::query()->firstOrCreate(['user_id' => auth()->user()->id]);

        $cart->removeItem($product);

        return redirect()->back()->with('flash', [
            'message' => 'Item remove to cart!',
            'type' => 'success'
        ]);
    }

    public function update(Request $request)
    {
        $qty = $request->input('qty');
        $id = $request->input('id');
        $type = $request->input('type');

        $product = Product::findOrFail($id);

        $cart = Cart::query()->firstOrCreate(['user_id' => auth()->user()->id]);

        $type === 'increase' ? $cart->increaseQuantity(item: $product, quantity: $qty) : $cart->decreaseQuantity(item: $product, quantity: $qty);

        return redirect()->back()->with('success', 'Item updated to cart');

    }

    public function checkout(Request $request)
    {
        $cartModel = Cart::query()->firstOrCreate(['user_id' => auth()->id()]);
        $cart = [
            'items' => [],
            'total' => $cartModel->calculatedPriceByQuantity(),
            'count' => $cartModel->items()->count(),
        ];
        foreach ($cartModel->items as $item) {
            $cart['items'][] = [
                'id' => $item->id,
                'name' => $item->itemable->name ?? '',
                'price' => $item->itemable->price ?? 0,
                'qty' => $item->quantity,
            ];
        }

        return Inertia::render('checkout/index', ['cart' => $cart]);
    }
}
