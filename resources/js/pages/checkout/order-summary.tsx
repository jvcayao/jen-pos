interface OrderSummaryProps {
    subtotal: number;
    discount: number;
    delivery: number;
    tax: number;
}

export const OrderSummary = ({
    subtotal,
    discount,
    tax,
}: OrderSummaryProps) => {
    const total = subtotal - discount + tax;

    return (
        <div className="border-cart-border rounded-lg border bg-card p-6">
            <h2 className="mb-6 text-xl font-semibold">Order Summary</h2>

            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <span className="text-muted-foreground">Discount</span>
                    <span className="font-medium">${discount.toFixed(2)}</span>
                </div>

                <div className="flex items-center justify-between">
                    <span className="text-muted-foreground">Tax</span>
                    <span className="font-medium">${tax.toFixed(2)}</span>
                </div>

                <hr className="border-cart-border" />

                <div className="flex items-center justify-between">
                    <span className="text-lg font-semibold">Total</span>
                    <span className="text-xl font-bold">
                        ${total.toFixed(2)}
                    </span>
                </div>
            </div>
        </div>
    );
};
