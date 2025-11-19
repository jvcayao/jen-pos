import { Button } from '@/components/ui/button';

export const PaymentMethods = () => {
    const paymentMethods = [
        { id: 'paypal', name: 'PayPal', icon: '💳' },
        { id: 'stripe', name: 'Stripe', icon: '$' },
        { id: 'mastercard', name: 'Mastercard', icon: '💳' },
        { id: 'bitcoin', name: 'Bitcoin', icon: '₿' },
    ];

    return (
        <div className="border-cart-border rounded-lg border bg-card p-6">
            <h2 className="mb-6 text-xl font-semibold">Payment Method</h2>

            <div className="mb-6 grid grid-cols-4 gap-3">
                {paymentMethods.map((method) => (
                    <Button
                        key={method.id}
                        variant="outline"
                        className="aspect-square h-12 p-0 hover:border-primary hover:bg-primary hover:text-primary-foreground"
                    >
                        <span className="text-lg">{method.icon}</span>
                    </Button>
                ))}
            </div>

            <Button className="h-12 w-full bg-primary text-primary-foreground hover:bg-primary/90">
                Check Out
            </Button>
        </div>
    );
};
