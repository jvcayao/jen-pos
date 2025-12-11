<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Product;
use Illuminate\Http\Request;
use Binafy\LaravelCart\Models\Cart;
use App\Http\Resources\CartResource;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Http\Requests\RemoveFromCartRequest;

class CartController extends Controller
{
    public function index(): Response
    {
        $cart = $this->getCartData();

        return Inertia::render('menu/index', ['cart' => $cart]);
    }

    public function addToCart(AddToCartRequest $request): RedirectResponse
    {
        $product = Product::findOrFail($request->validated('id'));
        $cart = $this->getUserCart();

        // Check stock availability
        if (!$product->isInStock()) {
            return back()->with('flash', [
                'message' => "'{$product->name}' is out of stock!",
                'type' => 'error',
            ]);
        }

        $existingItem = $cart->items()->where('itemable_id', $product->id)->first();

        if ($existingItem) {
            // Check if adding more would exceed stock
            if (!$product->isInStock($existingItem->quantity + 1)) {
                return back()->with('flash', [
                    'message' => "Not enough stock for '{$product->name}'!",
                    'type' => 'error',
                ]);
            }

            $existingItem->increment('quantity');

            return back()->with('flash', [
                'message' => 'Item quantity increased!',
                'type' => 'success',
            ]);
        }

        $cart->items()->create([
            'itemable_id' => $product->id,
            'itemable_type' => Product::class,
            'quantity' => 1,
        ]);

        return back()->with('flash', [
            'message' => 'Item added to cart!',
            'type' => 'success',
        ]);
    }

    public function addByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $product = Product::where('barcode', $request->barcode)
            ->orWhere('sku', $request->barcode)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        if (!$product->isInStock()) {
            return response()->json([
                'success' => false,
                'message' => "'{$product->name}' is out of stock!",
            ], 400);
        }

        $cart = $this->getUserCart();
        $existingItem = $cart->items()->where('itemable_id', $product->id)->first();

        if ($existingItem) {
            if (!$product->isInStock($existingItem->quantity + 1)) {
                return response()->json([
                    'success' => false,
                    'message' => "Not enough stock for '{$product->name}'!",
                ], 400);
            }

            $existingItem->increment('quantity');
        } else {
            $cart->items()->create([
                'itemable_id' => $product->id,
                'itemable_type' => Product::class,
                'quantity' => 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "'{$product->name}' added to cart!",
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
            ],
        ]);
    }

    public function update(UpdateCartRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $cart = $this->getUserCart();

        $cartItem = $cart->items()
            ->where('itemable_id', $validated['id'])
            ->where('itemable_type', Product::class)
            ->first();

        if (!$cartItem) {
            return back()->with('flash', [
                'message' => 'Item not found in cart!',
                'type' => 'error',
            ]);
        }

        match ($validated['type']) {
            'increase' => $cartItem->increment('quantity', $validated['qty']),
            'decrease' => $cartItem->decrement('quantity', $validated['qty']),
        };

        if ($cartItem->quantity <= 0) {
            $cartItem->delete();
        }

        return back()->with('flash', [
            'message' => 'Cart updated successfully',
            'type' => 'success',
        ]);
    }

    public function remove(RemoveFromCartRequest $request): RedirectResponse
    {
        $cart = $this->getUserCart();

        $cart->items()
            ->where('itemable_id', $request->validated('id'))
            ->where('itemable_type', Product::class)
            ->delete();

        return back()->with('flash', [
            'message' => 'Item removed from cart',
            'type' => 'success',
        ]);
    }

    public function checkout(): Response
    {
        $cart = $this->getCartData();

        return Inertia::render('checkout/index', ['cart' => $cart]);
    }

    private function getUserCart(): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => auth()->id()]);
    }

    private function getCartData(): array
    {
        $cartModel = $this->getUserCart();

        return CartResource::fromCart($cartModel);
    }
}
