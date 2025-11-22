import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { router } from '@inertiajs/react';
import { ImageIcon, ShoppingBag, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';

type CartItem = {
    id: number;
    name: string;
    price: number;
    qty: number;
    image_url?: string | null;
};
type CartState = {
    items: CartItem[];
    total: number;
    count: number;
};

export default function CartSidebar({
    open,
    onOpenChange,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const [cart, setCart] = useState<CartState>({
        items: [],
        total: 0,
        count: 0,
    });
    const [loading, setLoading] = useState(false);

    function fetchCart() {
        setLoading(true);
        router.get(
            '/cart',
            {},
            {
                onSuccess: (page) => {
                    if (
                        page.props.cart &&
                        Array.isArray(page.props.cart.items)
                    ) {
                        setCart(page.props.cart);
                    } else {
                        setCart({ items: [], total: 0, count: 0 });
                    }
                    setLoading(false);
                },
                onError: () => setLoading(false),
                preserveState: true,
                preserveScroll: true,
                only: ['cart'],
            },
        );
    }

    useEffect(() => {
        // eslint-disable-next-line
        if (open) fetchCart();
    }, [open]);

    function removeItem(id: number) {
        setLoading(true);
        router.post(
            '/cart/remove',
            { id },
            {
                onSuccess: fetchCart,
                onError: () => setLoading(false),
                preserveState: true,
                preserveScroll: true,
                only: ['cart'],
            },
        );
    }

    function updateQty(id: number, qty: number, type: string) {
        setLoading(true);
        router.post(
            '/cart/update',
            { id, qty, type },
            {
                onSuccess: fetchCart,
                onError: () => setLoading(false),
            },
        );
    }

    function handleProceedToCheckout() {
        setLoading(true);
        router.get(
            '/cart/checkout',
            {},
            {
                onError: () => setLoading(false),
                preserveState: true,
                preserveScroll: true,
            },
        );
    }

    return (
        <>
            <Sheet open={open} onOpenChange={onOpenChange}>
                <SheetContent side="right" className="w-full max-w-sm p-0">
                    <SheetHeader className="flex flex-row items-center justify-between border-b px-6 py-4">
                        <SheetTitle className="flex items-center gap-2 text-lg font-semibold">
                            <ShoppingBag className="h-5 w-5" /> Cart (
                            {cart.count})
                        </SheetTitle>
                    </SheetHeader>
                    <div className="flex h-[calc(100vh-120px)] flex-col gap-2 overflow-y-auto p-6">
                        {loading ? (
                            <div className="text-center text-muted-foreground">
                                Loading...
                            </div>
                        ) : cart.items.length === 0 ? (
                            <div className="text-center text-muted-foreground">
                                Your cart is empty.
                            </div>
                        ) : (
                            cart.items.map((item) => (
                                <Card key={item.id} className="mb-2">
                                    <CardHeader className="flex flex-row items-center justify-between">
                                        <CardTitle className="truncate text-base font-medium">
                                            {item.name}
                                        </CardTitle>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => removeItem(item.id)}
                                        >
                                            <Trash2 className="h-4 w-4 text-red-500" />
                                        </Button>
                                    </CardHeader>
                                    <CardContent className="flex items-center gap-2 py-2">
                                        {item.image_url ? (
                                            <img
                                                src={item.image_url}
                                                alt={item.name}
                                                className="aspect-[4/3] w-full object-cover"
                                            />
                                        ) : (
                                            <div className="aspect-[4/3] w-full items-center justify-center bg-muted/40 text-muted-foreground">
                                                <div className="flex h-full w-full items-center justify-center gap-2 text-sm">
                                                    <ImageIcon className="h-5 w-5" />{' '}
                                                    No image
                                                </div>
                                            </div>
                                        )}
                                        <div className="flex-1">
                                            <div className="text-sm text-muted-foreground">
                                                ₱{Number(item.price).toFixed(2)}
                                            </div>
                                            <div className="mt-1 flex items-center gap-2">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() =>
                                                        updateQty(
                                                            item.id,
                                                            1,
                                                            'decrease',
                                                        )
                                                    }
                                                    disabled={item.qty <= 1}
                                                >
                                                    -
                                                </Button>
                                                <span className="px-2 text-sm">
                                                    {item.qty}
                                                </span>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() =>
                                                        updateQty(
                                                            item.id,
                                                            1,
                                                            'increase',
                                                        )
                                                    }
                                                >
                                                    +
                                                </Button>
                                            </div>
                                        </div>
                                    </CardContent>
                                    <CardFooter className="flex justify-end">
                                        <div className="font-semibold">
                                            ₱
                                            {Number(
                                                item.price * item.qty,
                                            ).toFixed(2)}
                                        </div>
                                    </CardFooter>
                                </Card>
                            ))
                        )}
                    </div>
                    <div className="flex flex-col gap-2 border-t px-6 py-4">
                        <div className="flex items-center justify-between text-base font-semibold">
                            <span>Total</span>
                            <span>₱{Number(cart.total).toFixed(2)}</span>
                        </div>
                        <Button
                            className="mt-2 w-full"
                            disabled={cart.items.length === 0}
                            onClick={() => {
                                handleProceedToCheckout();
                            }}
                        >
                            Checkout
                        </Button>
                    </div>
                </SheetContent>
            </Sheet>
        </>
    );
}
