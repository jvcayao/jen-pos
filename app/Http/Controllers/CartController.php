<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Binafy\LaravelCart\Models\Cart;
use App\Http\Resources\CartResource;
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

        if ($cart->items()->where('itemable_id', $product->id)->exists()) {
            return back()->with('flash', [
                'message' => 'Item already in cart!',
                'type' => 'warning',
            ]);
        }

        $cart->storeItem([
            'itemable' => $product,
            'quantity' => 1,
        ]);

        return back()->with('flash', [
            'message' => 'Item added to cart!',
            'type' => 'success',
        ]);
    }

    public function update(UpdateCartRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $product = Product::findOrFail($validated['id']);
        $cart = $this->getUserCart();

        match ($validated['type']) {
            'increase' => $cart->increaseQuantity(item: $product, quantity: $validated['qty']),
            'decrease' => $cart->decreaseQuantity(item: $product, quantity: $validated['qty']),
        };

        return back()->with('flash', [
            'message' => 'Cart updated successfully',
            'type' => 'success',
        ]);
    }

    public function remove(RemoveFromCartRequest $request): RedirectResponse
    {
        $product = Product::findOrFail($request->validated('id'));
        $cart = $this->getUserCart();

        $cart->removeItem($product);

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
