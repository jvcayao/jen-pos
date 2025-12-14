import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { CheckoutStudent, PaymentMethod } from '@/types/checkout.d';
import { router } from '@inertiajs/react';
import { Keyboard, Split, Tag, Wallet } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

const paymentMethods: PaymentMethod[] = [
    {
        id: 'cash',
        name: 'Cash',
        logos: ['Cash'],
        component: 'external',
    },
    {
        id: 'gcash',
        name: 'G-Cash',
        logos: ['G-Cash'],
        component: 'external',
    },
    {
        id: 'wallet',
        name: 'Student Wallet',
        logos: ['Wallet'],
        component: 'external',
    },
];

interface PaymentMethodsProps {
    disabled?: boolean;
    subtotal?: number;
    onDiscountApplied?: (discount: number, code: string) => void;
    selectedStudent: CheckoutStudent | null;
}

export const PaymentMethods = ({
    disabled = false,
    subtotal = 0,
    onDiscountApplied,
    selectedStudent,
}: PaymentMethodsProps) => {
    const [selectedPayment, setSelectedPayment] = useState('cash');
    const [selectedPayment2, setSelectedPayment2] = useState('');
    const [splitPayment, setSplitPayment] = useState(false);
    const [amount1, setAmount1] = useState('');
    const [amount2, setAmount2] = useState('');
    const [loading, setLoading] = useState(false);
    const [discountCode, setDiscountCode] = useState('');
    const [discountLoading, setDiscountLoading] = useState(false);
    const [discountApplied, setDiscountApplied] = useState<{
        discount: number;
        message: string;
    } | null>(null);
    const [discountError, setDiscountError] = useState('');
    const [notes, setNotes] = useState('');

    // subtotal is already VAT-inclusive, just apply discount
    const discountAmount = discountApplied?.discount || 0;
    const total = subtotal - discountAmount;

    // Helper to check if wallet payment is selected
    const isWalletPayment = selectedPayment === 'wallet';

    // Get the student's wallet balance
    const getSelectedWalletBalance = () => {
        if (!selectedStudent) return 0;
        return selectedStudent.wallet_balance;
    };

    // Check if student has a wallet assigned
    const hasWallet = () => {
        if (!selectedStudent) return false;
        return selectedStudent.has_wallet;
    };

    const handleApplyDiscount = async () => {
        if (!discountCode.trim()) return;

        setDiscountLoading(true);
        setDiscountError('');

        try {
            const response = await fetch('/orders/validate-discount', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',
                },
                body: JSON.stringify({ code: discountCode, subtotal }),
            });

            const data = await response.json();

            if (data.valid) {
                setDiscountApplied({
                    discount: data.discount,
                    message: data.message,
                });
                onDiscountApplied?.(data.discount, discountCode);
            } else {
                setDiscountError(data.message);
                setDiscountApplied(null);
            }
        } catch {
            setDiscountError('Failed to validate discount code');
        }

        setDiscountLoading(false);
    };

    const handleRemoveDiscount = () => {
        setDiscountCode('');
        setDiscountApplied(null);
        setDiscountError('');
        onDiscountApplied?.(0, '');
    };

    const handleProceedPayment = () => {
        // Validate wallet payment has student selected
        if (isWalletPayment && !selectedStudent) {
            return;
        }

        // Check if student has a wallet
        if (isWalletPayment && selectedStudent && !hasWallet()) {
            return;
        }

        // Check wallet balance
        if (
            isWalletPayment &&
            selectedStudent &&
            getSelectedWalletBalance() < total
        ) {
            return;
        }

        setLoading(true);

        const payload: Record<string, unknown> = {
            payment_method: selectedPayment,
            notes,
        };

        if (discountApplied) {
            payload.discount_code = discountCode;
        }

        if (selectedStudent) {
            payload.student_id = selectedStudent.id;
        }

        if (isWalletPayment && selectedStudent) {
            payload.wallet_type = selectedStudent.wallet_type;
        }

        if (splitPayment && selectedPayment2) {
            payload.payment_method_2 = selectedPayment2;
            payload.amount_1 = parseFloat(amount1) || 0;
            payload.amount_2 = parseFloat(amount2) || 0;
        }

        router.post('/orders', payload as Parameters<typeof router.post>[1], {
            onFinish: () => setLoading(false),
        });
    };

    // Keyboard shortcuts
    const handleKeyPress = useCallback(
        (e: KeyboardEvent) => {
            if (disabled || loading) return;

            // Alt + 1-9 for quick payment selection
            if (e.altKey && !e.ctrlKey && !e.shiftKey) {
                const num = parseInt(e.key);
                if (num >= 1 && num <= paymentMethods.length) {
                    e.preventDefault();
                    setSelectedPayment(paymentMethods[num - 1].id);
                }
            }

            // Enter to proceed payment (when not in input)
            if (e.key === 'Enter' && e.ctrlKey) {
                e.preventDefault();
                handleProceedPayment();
            }

            // Alt + S to toggle split payment
            if (e.altKey && e.key.toLowerCase() === 's') {
                e.preventDefault();
                setSplitPayment((prev) => !prev);
            }

            // Alt + D to focus discount code input
            if (e.altKey && e.key.toLowerCase() === 'd') {
                e.preventDefault();
                document.getElementById('discount-code')?.focus();
            }
        },
        [disabled, loading, handleProceedPayment],
    );

    useEffect(() => {
        window.addEventListener('keydown', handleKeyPress);
        return () => window.removeEventListener('keydown', handleKeyPress);
    }, [handleKeyPress]);

    // Auto-calculate second amount in split payment
    useEffect(() => {
        if (splitPayment && amount1) {
            const firstAmount = parseFloat(amount1) || 0;
            const remaining = Math.max(0, total - firstAmount);
            setAmount2(remaining.toFixed(2));
        }
    }, [amount1, total, splitPayment]);

    const formatCurrency = (value: number) => {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(value);
    };

    const isWalletPaymentValid =
        !isWalletPayment ||
        (selectedStudent && hasWallet() && getSelectedWalletBalance() >= total);

    const walletInsufficientBalance =
        isWalletPayment &&
        selectedStudent &&
        hasWallet() &&
        getSelectedWalletBalance() < total;

    const walletNotExists =
        isWalletPayment && selectedStudent !== null && !hasWallet();

    return (
        <div className="space-y-6">
            {/* Discount Code Section */}
            <div className="rounded-lg border bg-card p-4">
                <div className="mb-3 flex items-center gap-2">
                    <Tag className="h-4 w-4" />
                    <h3 className="font-medium">Discount Code</h3>
                    <Badge variant="outline" className="text-xs">
                        Alt+D
                    </Badge>
                </div>
                <div className="flex gap-2">
                    <Input
                        id="discount-code"
                        placeholder="Enter promo code"
                        value={discountCode}
                        onChange={(e) =>
                            setDiscountCode(e.target.value.toUpperCase())
                        }
                        disabled={disabled || !!discountApplied}
                        onKeyDown={(e) =>
                            e.key === 'Enter' && handleApplyDiscount()
                        }
                    />
                    {discountApplied ? (
                        <Button
                            variant="outline"
                            onClick={handleRemoveDiscount}
                            disabled={disabled}
                        >
                            Remove
                        </Button>
                    ) : (
                        <Button
                            onClick={handleApplyDiscount}
                            disabled={
                                disabled ||
                                discountLoading ||
                                !discountCode.trim()
                            }
                        >
                            {discountLoading ? 'Checking...' : 'Apply'}
                        </Button>
                    )}
                </div>
                {discountApplied && (
                    <p className="mt-2 text-sm text-green-600">
                        {discountApplied.message}
                    </p>
                )}
                {discountError && (
                    <p className="mt-2 text-sm text-red-600">{discountError}</p>
                )}
            </div>

            {/* Payment Method Section */}
            <div className="rounded-lg border bg-card p-4">
                <div className="mb-3 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <h3 className="font-medium">Payment Method</h3>
                        <Badge variant="outline" className="text-xs">
                            Alt+1-4
                        </Badge>
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => setSplitPayment(!splitPayment)}
                        className="flex items-center gap-1"
                    >
                        <Split className="h-4 w-4" />
                        Split
                        <Badge variant="outline" className="ml-1 text-xs">
                            Alt+S
                        </Badge>
                    </Button>
                </div>

                <div className="space-y-3">
                    {paymentMethods.map((method, index) => (
                        <Card
                            key={method.id}
                            className={`cursor-pointer transition-all duration-200 ${
                                selectedPayment === method.id
                                    ? 'border-primary/20 ring-2 ring-primary'
                                    : 'hover:border-muted-foreground/20'
                            } ${disabled ? 'pointer-events-none opacity-50' : ''}`}
                            onClick={() =>
                                !disabled && setSelectedPayment(method.id)
                            }
                        >
                            <CardContent className="p-3">
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
                                        <div className="flex items-center gap-2">
                                            {method.id.startsWith(
                                                'wallet-',
                                            ) && <Wallet className="h-4 w-4" />}
                                            <span className="font-medium">
                                                {method.name}
                                            </span>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Badge
                                            variant="secondary"
                                            className="text-xs"
                                        >
                                            {index + 1}
                                        </Badge>
                                        <div className="rounded bg-muted px-2 py-1 text-xs font-semibold text-muted-foreground">
                                            {method.logos[0]}
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}

                    {splitPayment && (
                        <div className="mt-4 space-y-4 rounded-lg border border-dashed p-4">
                            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                <Split className="h-4 w-4" />
                                <span>Split Payment</span>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <Label className="text-xs">
                                        First Payment ({selectedPayment})
                                    </Label>
                                    <Input
                                        type="number"
                                        placeholder="0.00"
                                        value={amount1}
                                        onChange={(e) =>
                                            setAmount1(e.target.value)
                                        }
                                        disabled={disabled}
                                    />
                                </div>
                                <div>
                                    <Label className="text-xs">
                                        Second Payment Method
                                    </Label>
                                    <select
                                        className="h-9 w-full rounded border border-input bg-background px-3 text-sm"
                                        value={selectedPayment2}
                                        onChange={(e) =>
                                            setSelectedPayment2(e.target.value)
                                        }
                                        disabled={disabled}
                                    >
                                        <option value="">Select...</option>
                                        {paymentMethods
                                            .filter(
                                                (m) =>
                                                    m.id !== selectedPayment &&
                                                    m.id !== 'wallet',
                                            )
                                            .map((method) => (
                                                <option
                                                    key={method.id}
                                                    value={method.id}
                                                >
                                                    {method.name}
                                                </option>
                                            ))}
                                    </select>
                                </div>
                            </div>

                            {selectedPayment2 && (
                                <div>
                                    <Label className="text-xs">
                                        Second Amount ({selectedPayment2})
                                    </Label>
                                    <Input
                                        type="number"
                                        placeholder="0.00"
                                        value={amount2}
                                        disabled
                                        className="bg-muted"
                                    />
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>

            {/* Notes Section */}
            <div className="rounded-lg border bg-card p-4">
                <Label className="mb-2 block">Order Notes (optional)</Label>
                <Input
                    placeholder="Add special instructions..."
                    value={notes}
                    onChange={(e) => setNotes(e.target.value)}
                    disabled={disabled}
                />
            </div>

            {/* Keyboard Shortcuts Help */}
            <div className="flex items-center justify-center gap-4 text-xs text-muted-foreground">
                <div className="flex items-center gap-1">
                    <Keyboard className="h-3 w-3" />
                    <span>Shortcuts:</span>
                </div>
                <span>Ctrl+Enter = Pay</span>
                <span>Alt+S = Split</span>
                <span>Alt+D = Discount</span>
            </div>

            <Button
                className="h-12 w-full bg-primary text-primary-foreground hover:bg-primary/90"
                onClick={handleProceedPayment}
                disabled={
                    disabled ||
                    loading ||
                    (splitPayment && (!amount1 || !selectedPayment2)) ||
                    (isWalletPayment && !selectedStudent) ||
                    !isWalletPaymentValid ||
                    walletNotExists
                }
            >
                {loading ? 'Processing...' : `Pay ${formatCurrency(total)}`}
            </Button>
        </div>
    );
};
