<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Order;
use Inertia\Response;
use App\Models\Student;
use App\Models\OrderItem;
use App\Models\DiscountCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Binafy\LaravelCart\Models\Cart;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\CreateOrderRequest;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = Order::with(['items.product', 'user'])
            ->where('user_id', auth()->id())
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), fn ($q) => $q->where('uuid', 'like', '%'.$request->search.'%'))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(fn ($order) => [
                'id' => $order->id,
                'uuid' => $order->uuid,
                'total' => $order->total,
                'vat' => $order->vat,
                'discount' => $order->discount,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'is_payed' => $order->is_payed,
                'notes' => $order->notes,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->item,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'total' => $item->total,
                    'image_url' => $item->product?->image_url,
                ]),
                'created_at' => $order->created_at->format('M d, Y h:i A'),
            ]);

        return Inertia::render('orders/index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load(['items.product', 'user', 'cashier']);

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // VAT-inclusive: Subtotal (Net of VAT) = Total + Discount - VAT is already included
        // For display: show Total (VAT-inclusive), VAT Amount, and Vatable Sales (Net of VAT)
        $vatableSales = ($order->total + $order->discount) - $order->vat;

        return Inertia::render('orders/show', [
            'order' => [
                'id' => $order->id,
                'uuid' => $order->uuid,
                'total' => $order->total,
                'subtotal' => $vatableSales, // Vatable Sales (Net of VAT)
                'vat' => $order->vat,
                'discount' => $order->discount,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'is_payed' => $order->is_payed,
                'notes' => $order->notes,
                'customer' => $order->user?->name ?? 'Walk-in',
                'cashier' => $order->cashier?->name ?? 'System',
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->item,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'total' => $item->total,
                    'image_url' => $item->product?->image_url,
                ]),
                'created_at' => $order->created_at->format('M d, Y h:i A'),
            ],
        ]);
    }

    public function store(CreateOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $cart = Cart::query()->where('user_id', auth()->id())->first();

        if (!$cart || $cart->items()->count() === 0) {
            return back()->with('flash', [
                'message' => 'Your cart is empty!',
                'type' => 'error',
            ]);
        }

        $paymentMethod = $validated['payment_method'];
        $paymentConfig = config("payment.methods.{$paymentMethod}");

        if (!$paymentConfig || !$paymentConfig['enabled']) {
            return back()->with('flash', [
                'message' => 'Invalid payment method!',
                'type' => 'error',
            ]);
        }

        // Handle wallet payment - requires student
        $student = null;
        if ($paymentMethod === 'wallet') {
            if (empty($validated['student_id'])) {
                return back()->with('flash', [
                    'message' => 'Student is required for wallet payment!',
                    'type' => 'error',
                ]);
            }

            $student = Student::find($validated['student_id']);
            if (!$student) {
                return back()->with('flash', [
                    'message' => 'Student not found!',
                    'type' => 'error',
                ]);
            }

            if (!$student->is_active) {
                return back()->with('flash', [
                    'message' => 'Student account is inactive!',
                    'type' => 'error',
                ]);
            }
        }

        try {
            DB::beginTransaction();

            $cartItems = $cart->items()->with('itemable')->get();

            // Check stock availability
            foreach ($cartItems as $cartItem) {
                $product = $cartItem->itemable;
                if ($product && !$product->isInStock($cartItem->quantity)) {
                    DB::rollBack();

                    return back()->with('flash', [
                        'message' => "'{$product->name}' is out of stock!",
                        'type' => 'error',
                    ]);
                }
            }

            // VAT-INCLUSIVE PRICING
            // Prices already include VAT, we need to extract VAT from VATable items
            $taxRate = config('payment.tax_rate', 0.12);

            // Total = sum of all item prices (prices are VAT-inclusive)
            $grossTotal = 0;
            $vatableGrossTotal = 0;

            foreach ($cartItems as $cartItem) {
                $product = $cartItem->itemable;
                if ($product) {
                    $itemTotal = $product->price * $cartItem->quantity;
                    $grossTotal += $itemTotal;

                    // Only include in VATable amount if product has VAT
                    if ($product->has_vat ?? true) {
                        $vatableGrossTotal += $itemTotal;
                    }
                }
            }

            $discountAmount = 0;
            $discountCode = null;

            // Apply discount code if provided
            if (!empty($validated['discount_code'])) {
                $discountCode = DiscountCode::where('code', strtoupper($validated['discount_code']))->first();
                if ($discountCode && $discountCode->isValid()) {
                    $discountAmount = $discountCode->calculateDiscount($grossTotal);
                }
            }

            // Calculate final total after discount
            $total = $grossTotal - $discountAmount;

            // Calculate VAT from VATable items using VAT-inclusive formula: VAT = Amount × (12/112)
            // Apply discount proportionally to VATable items
            $discountRatio = $grossTotal > 0 ? ($grossTotal - $discountAmount) / $grossTotal : 1;
            $adjustedVatableAmount = $vatableGrossTotal * $discountRatio;
            $vat = $adjustedVatableAmount * ($taxRate / (1 + $taxRate));

            // Check wallet balance for wallet payments
            if ($paymentMethod === 'wallet' && $student) {
                if ($student->balanceFloatNum < $total) {
                    DB::rollBack();

                    return back()->with('flash', [
                        'message' => 'Insufficient wallet balance! Current balance: ₱'.number_format($student->balanceFloatNum, 2),
                        'type' => 'error',
                    ]);
                }

                // Deduct from wallet
                $student->withdrawFloat($total, [
                    'description' => 'Payment for order',
                    'order_total' => $total,
                ]);
            }

            $order = Order::create([
                'user_id' => auth()->id(),
                'account_id' => auth()->id(),
                'cashier_id' => auth()->id(),
                'student_id' => $student?->id,
                'source' => 'pos',
                'total' => $total,
                'discount' => $discountAmount,
                'shipping' => 0,
                'vat' => $vat,
                'status' => 'confirm',
                'is_payed' => true,
                'payment_method' => $paymentMethod,
                'payment_vendor' => $paymentConfig['vendor'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($cartItems as $cartItem) {
                $product = $cartItem->itemable;

                if (!$product) {
                    continue;
                }

                $itemTotal = $product->price * $cartItem->quantity;
                $itemHasVat = $product->has_vat ?? true;
                // VAT-inclusive: VAT = ItemTotal × (12/112) for VATable items
                $itemVat = $itemHasVat ? ($itemTotal * $discountRatio * ($taxRate / (1 + $taxRate))) : 0;

                OrderItem::create([
                    'order_id' => $order->id,
                    'account_id' => auth()->id(),
                    'product_id' => $product->id,
                    'item' => $product->name,
                    'price' => $product->price,
                    'qty' => $cartItem->quantity,
                    'total' => $itemTotal,
                    'discount' => 0,
                    'vat' => round($itemVat, 2),
                ]);

                // Decrement stock
                $product->decrementStock($cartItem->quantity);
            }

            // Increment discount code usage
            if ($discountCode) {
                $discountCode->incrementUsage();
            }

            $cart->emptyCart();

            DB::commit();

            return redirect()->route('orders.show', $order)->with('flash', [
                'message' => 'Order created successfully!',
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('flash', [
                'message' => 'Failed to create order. Please try again.',
                'type' => 'error',
            ]);
        }
    }

    public function cancel(): RedirectResponse
    {
        $cart = Cart::query()->where('user_id', auth()->id())->first();

        if ($cart) {
            $cart->emptyCart();
        }

        return redirect()->route('menu.index')->with('flash', [
            'message' => 'Order cancelled successfully.',
            'type' => 'success',
        ]);
    }

    public function validateDiscount(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $code = DiscountCode::where('code', strtoupper($request->code))->first();

        if (!$code) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid discount code',
            ]);
        }

        if (!$code->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'This discount code has expired or reached its usage limit',
            ]);
        }

        if ($code->min_order_amount && $request->subtotal < $code->min_order_amount) {
            return response()->json([
                'valid' => false,
                'message' => 'Minimum order amount is ₱'.number_format($code->min_order_amount, 2),
            ]);
        }

        $discount = $code->calculateDiscount($request->subtotal);

        return response()->json([
            'valid' => true,
            'discount' => $discount,
            'type' => $code->type,
            'value' => $code->value,
            'message' => $code->type === 'percentage'
                ? "{$code->value}% discount applied"
                : '₱'.number_format($code->value, 2).' discount applied',
        ]);
    }
}
