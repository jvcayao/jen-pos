import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import type { CartItemData, CheckoutPageProps } from '@/types/checkout.d';
import { CartItem } from '@/pages/checkout/cart-item';
import { OrderSummary } from '@/pages/checkout/order-summary';
import { PaymentMethods } from '@/pages/checkout/payment-method';
import { Head, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Checkout', href: '/checkout' }];

export default function Checkout() {
    const { cart } = usePage<CheckoutPageProps>().props;
    const items = cart.items || [];
    const total = cart.total || 0;

    const updateQuantity = (_id: string, _qty: number) => {};
    const removeItem = (_id: string) => {};

    const discount = 0;
    const tax = 0.12;
    const delivery = 0;

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
                                    {items.map((item: CartItemData) => (
                                        <CartItem
                                            key={item.id}
                                            id={String(item.id)}
                                            name={item.name}
                                            color={item.color ?? ''}
                                            price={item.price}
                                            quantity={item.qty}
                                            image={item.image ?? ''}
                                            onUpdateQuantity={updateQuantity}
                                            onRemove={removeItem}
                                        />
                                    ))}
                                </div>
                            </div>
                            <div className="mt-6 flex flex-col gap-4 sm:flex-row">
                                <Button
                                    variant="outline"
                                    className="flex items-center gap-2"
                                >
                                    <ArrowLeft className="h-4 w-4" />
                                    Back
                                </Button>
                                <Button
                                    variant="destructive"
                                    className="bg-destructive hover:bg-destructive/90"
                                >
                                    Cancel Order
                                </Button>
                            </div>
                        </div>
                        <div className="space-y-6">
                            <OrderSummary
                                subtotal={total}
                                discount={discount}
                                tax={tax}
                                delivery={delivery}
                            />
                            <PaymentMethods />
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
