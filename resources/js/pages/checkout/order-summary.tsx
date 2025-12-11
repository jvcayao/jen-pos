import type { OrderSummaryProps } from '@/types/checkout.d';

export const OrderSummary = ({
    total,
    vatableSales,
    vatAmount,
    discount,
    taxRate,
}: OrderSummaryProps) => {
    // For VAT-inclusive pricing, display order:
    // 1. Total Amount (VAT-inclusive) - what customer pays
    // 2. Less: Discount (if any)
    // 3. Vatable Sales (Net of VAT)
    // 4. VAT Amount (12%)

    const finalTotal = total - discount;

    return (
        <div className="border-cart-border rounded-lg border bg-card p-6">
            <h2 className="mb-6 text-xl font-semibold">Order Summary</h2>

            <div className="space-y-4">
                {/* Total Amount (VAT-inclusive) */}
                <div className="flex items-center justify-between">
                    <span className="text-muted-foreground">Total Amount</span>
                    <span className="font-medium">₱{total.toFixed(2)}</span>
                </div>

                {/* Discount */}
                {discount > 0 && (
                    <div className="flex items-center justify-between text-green-600">
                        <span>Less: Discount</span>
                        <span className="font-medium">
                            -₱{discount.toFixed(2)}
                        </span>
                    </div>
                )}

                <hr className="border-cart-border" />

                {/* VAT Breakdown */}
                <div className="flex items-center justify-between">
                    <span className="text-muted-foreground">Vatable Sales</span>
                    <span className="font-medium">
                        ₱{vatableSales.toFixed(2)}
                    </span>
                </div>

                <div className="flex items-center justify-between">
                    <span className="text-muted-foreground">
                        VAT ({(taxRate * 100).toFixed(0)}%)
                    </span>
                    <span className="font-medium">₱{vatAmount.toFixed(2)}</span>
                </div>

                <hr className="border-cart-border" />

                {/* Amount Due */}
                <div className="flex items-center justify-between">
                    <span className="text-lg font-semibold">Amount Due</span>
                    <span className="text-xl font-bold">
                        ₱{finalTotal.toFixed(2)}
                    </span>
                </div>
            </div>
        </div>
    );
};
