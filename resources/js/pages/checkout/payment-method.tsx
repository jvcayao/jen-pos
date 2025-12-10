import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { PaymentMethod } from '@/types/checkout.d';
import { useState } from 'react';

const paymentMethods: PaymentMethod[] = [
    {
        id: 'cash',
        name: 'Cash',
        logos: ['Cash'],
        component: 'external',
    },
    {
        id: 'g-cash',
        name: 'G Cash',
        logos: ['g-cash'],
        component: 'external',
    },
];

export const PaymentMethods = () => {
    const [selectedPayment, setSelectedPayment] = useState('cash');

    return (
        <div className="border-cart-border rounded-lg border bg-card p-6">
            <h2 className="mb-6 text-xl font-semibold">Payment Method</h2>

            <div className="space-y-4">
                {paymentMethods.map((method) => (
                    <Card
                        key={method.id}
                        className={`cursor-pointer transition-all duration-200 ${
                            selectedPayment === method.id
                                ? 'border-primary/20 ring-2 ring-primary'
                                : 'hover:border-muted-foreground/20'
                        }`}
                        onClick={() => setSelectedPayment(method.id)}
                    >
                        <CardContent>
                            <div className="flex items-center justify-between">
                                <div className="flex items-center space-x-3">
                                    <div
                                        className={`flex h-4 w-4 items-center justify-center rounded-full border-2 ${
                                            selectedPayment === method.id
                                                ? 'border-primary bg-primary'
                                                : 'border-muted-foreground'
                                        }`}
                                    >
                                        {selectedPayment === method.id && (
                                            <div className="h-2 w-2 rounded-full bg-primary-foreground"></div>
                                        )}
                                    </div>
                                    <span className="font-medium text-foreground">
                                        {method.name}
                                    </span>
                                </div>
                                <div className="flex items-center space-x-2">
                                    {method.logos.map((logo) => (
                                        <div
                                            key={logo}
                                            className="rounded bg-muted px-2 py-1 text-xs font-semibold text-muted-foreground"
                                        >
                                            {logo}
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Card Form */}
                        </CardContent>
                    </Card>
                ))}
                <Button className="h-12 w-full bg-primary text-primary-foreground hover:bg-primary/90">
                    Proceed Payment
                </Button>
            </div>
        </div>
    );
};
