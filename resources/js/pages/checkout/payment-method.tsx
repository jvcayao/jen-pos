import { Button } from '@/components/ui/button';
import { Card, CardContent } from "@/components/ui/card";
import { useState } from 'react';
export const PaymentMethods = () => {
    const [selectedPayment, setSelectedPayment] = useState("card");
    const paymentMethods = [
        {
            id: "cash",
            name: "Cash",
            logos: ["Cash"],
            component: "external"
        },
        {
            id: "g-cash",
            name: "G Cash",
            logos: ["g-cash"],
            component: "external"
        }
    ];

    return (
        <div className="border-cart-border rounded-lg border bg-card p-6">
            <h2 className="mb-6 text-xl font-semibold">Payment Method</h2>

            <div className="space-y-4">
                {paymentMethods.map((method) => (
                    <Card
                        key={method.id}
                        className={`cursor-pointer transition-all duration-200 ${
                            selectedPayment === method.id
                                ? "ring-primary border-primary/20 ring-2"
                                : "hover:border-muted-foreground/20"
                        }`}
                        onClick={() => setSelectedPayment(method.id)}>
                        <CardContent>
                            <div className="flex items-center justify-between">
                                <div className="flex items-center space-x-3">
                                    <div
                                        className={`flex h-4 w-4 items-center justify-center rounded-full border-2 ${
                                            selectedPayment === method.id
                                                ? "border-primary bg-primary"
                                                : "border-muted-foreground"
                                        }`}>
                                        {selectedPayment === method.id && (
                                            <div className="bg-primary-foreground h-2 w-2 rounded-full"></div>
                                        )}
                                    </div>
                                    <span className="text-foreground font-medium">{method.name}</span>
                                </div>
                                <div className="flex items-center space-x-2">
                                    {method.logos.map((logo) => (
                                        <div
                                            key={logo}
                                            className="bg-muted text-muted-foreground rounded px-2 py-1 text-xs font-semibold">
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
