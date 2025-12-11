import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/app-layout';
import { CartItem } from '@/pages/checkout/cart-item';
import { OrderSummary } from '@/pages/checkout/order-summary';
import { PaymentMethods } from '@/pages/checkout/payment-method';
import { type BreadcrumbItem } from '@/types';
import type { CartItemData, CheckoutPageProps } from '@/types/checkout.d';
import { Head, router, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Checkout', href: '/checkout' },
];

export default function Checkout() {
    const { cart } = usePage<CheckoutPageProps>().props;
    const [cartItems, setCartItems] = useState<CartItemData[]>(
        cart.items || [],
    );
    const [cartSubtotal, setCartSubtotal] = useState(cart.subtotal || 0);
    const [cartVatAmount, setCartVatAmount] = useState(cart.vat_amount || 0);
    const [cartTotal, setCartTotal] = useState(cart.total || 0);
    const [taxRate, setTaxRate] = useState(cart.tax_rate || 0.12);
    const [loading, setLoading] = useState(false);
    const [showEmptyCartDialog, setShowEmptyCartDialog] = useState(
        cart.items.length === 0,
    );
    const [showCancelDialog, setShowCancelDialog] = useState(false);

    const discount = 0;

    const fetchCart = () => {
        router.get(
            '/cart/checkout',
            {},
            {
                onSuccess: (page) => {
                    const pageProps = page.props as CheckoutPageProps;
                    if (pageProps.cart && Array.isArray(pageProps.cart.items)) {
                        setCartItems(pageProps.cart.items);
                        setCartSubtotal(pageProps.cart.subtotal);
                        setCartVatAmount(pageProps.cart.vat_amount);
                        setCartTotal(pageProps.cart.total);
                        setTaxRate(pageProps.cart.tax_rate);
                        if (pageProps.cart.items.length === 0) {
                            setShowEmptyCartDialog(true);
                        }
                    }
                    setLoading(false);
                },
                onError: () => setLoading(false),
                preserveState: true,
                preserveScroll: true,
                only: ['cart'],
            },
        );
    };

    const updateQuantity = (id: string, qty: number) => {
        if (qty < 1) return;
        setLoading(true);
        const type =
            qty > (cartItems.find((item) => String(item.id) === id)?.qty || 0)
                ? 'increase'
                : 'decrease';
        router.post(
            '/cart/update',
            { id: Number(id), qty: 1, type },
            {
                onSuccess: fetchCart,
                onError: () => setLoading(false),
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const removeItem = (id: string) => {
        setLoading(true);
        router.post(
            '/cart/remove',
            { id: Number(id) },
            {
                onSuccess: fetchCart,
                onError: () => setLoading(false),
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleBack = () => {
        if (cartItems.length === 0) {
            setShowEmptyCartDialog(true);
        } else {
            router.get('/menu');
        }
    };

    const handleCancelOrder = () => {
        setShowCancelDialog(true);
    };

    const confirmCancelOrder = () => {
        setLoading(true);
        router.post(
            '/orders/cancel',
            {},
            {
                onSuccess: () => {
                    setShowCancelDialog(false);
                    router.get('/menu');
                },
                onError: () => {
                    setLoading(false);
                    setShowCancelDialog(false);
                },
            },
        );
    };

    const handleEmptyCartRedirect = () => {
        setShowEmptyCartDialog(false);
        router.get('/menu');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Checkout" />
            <div className="min-h-screen bg-background p-4 lg:p-8">
                <div className="mx-auto max-w-7xl">
                    <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
                        <div className="lg:col-span-2">
                            <div className="border-cart-border rounded-lg border bg-card p-6">
                                <h1 className="mb-6 text-2xl font-semibold">
                                    Cart Items
                                </h1>
                                <div className="space-y-4">
                                    {loading ? (
                                        <div className="py-8 text-center text-muted-foreground">
                                            Loading...
                                        </div>
                                    ) : cartItems.length === 0 ? (
                                        <div className="py-8 text-center text-muted-foreground">
                                            Your cart is empty.
                                        </div>
                                    ) : (
                                        cartItems.map((item: CartItemData) => (
                                            <CartItem
                                                key={item.id}
                                                id={String(item.id)}
                                                name={item.name}
                                                color={item.color ?? ''}
                                                price={item.price}
                                                quantity={item.qty}
                                                image={item.image ?? ''}
                                                onUpdateQuantity={
                                                    updateQuantity
                                                }
                                                onRemove={removeItem}
                                            />
                                        ))
                                    )}
                                </div>
                            </div>
                            <div className="mt-6 flex flex-col gap-4 sm:flex-row">
                                <Button
                                    variant="outline"
                                    className="flex items-center gap-2"
                                    onClick={handleBack}
                                    disabled={loading}
                                >
                                    <ArrowLeft className="h-4 w-4" />
                                    Back
                                </Button>
                                <Button
                                    variant="destructive"
                                    className="bg-destructive hover:bg-destructive/90"
                                    onClick={handleCancelOrder}
                                    disabled={loading || cartItems.length === 0}
                                >
                                    Cancel Order
                                </Button>
                            </div>
                        </div>
                        <div className="space-y-6">
                            <OrderSummary
                                total={cartTotal}
                                vatableSales={cartSubtotal}
                                vatAmount={cartVatAmount}
                                discount={discount}
                                taxRate={taxRate}
                            />
                            <PaymentMethods
                                disabled={loading || cartItems.length === 0}
                                subtotal={cartTotal}
                            />
                        </div>
                    </div>
                </div>
            </div>

            <Dialog
                open={showEmptyCartDialog}
                onOpenChange={setShowEmptyCartDialog}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Cart is Empty</DialogTitle>
                        <DialogDescription>
                            Your cart is empty. Please add items to your cart
                            before proceeding to checkout.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button onClick={handleEmptyCartRedirect}>
                            Go to Menu
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog open={showCancelDialog} onOpenChange={setShowCancelDialog}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Cancel Order</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to cancel this order? All
                            items in your cart will be removed.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setShowCancelDialog(false)}
                            disabled={loading}
                        >
                            No, Keep Order
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={confirmCancelOrder}
                            disabled={loading}
                        >
                            Yes, Cancel Order
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
