import CartSidebar from '@/components/cart-sidebar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import type { MenuPageProps, MenuProductCardProps } from '@/types/menu.d';
import { Head, router, usePage } from '@inertiajs/react';
import {
    AlertCircle,
    Barcode,
    Image as ImageIcon,
    Keyboard,
    Package,
    Search,
    ShoppingBag,
} from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Menu', href: '/menu' }];

function useQuerySync(initial: { search?: string; category?: string }) {
    const [search, setSearch] = useState(initial.search ?? '');
    const [category, setCategory] = useState(initial.category ?? '');

    useEffect(() => {
        const t = setTimeout(() => {
            const params: Record<string, string> = {};
            if (search) params.search = search;
            if (category) params.category = category;
            const queryString = new URLSearchParams(params).toString();
            const baseUrl = window.location.pathname;
            const url = queryString ? `${baseUrl}?${queryString}` : baseUrl;
            router.get(
                url,
                {},
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 300);
        return () => clearTimeout(t);
    }, [search, category]);

    return { search, setSearch, category, setCategory };
}

function ProductCard({ product }: { product: MenuProductCardProps['product'] }) {
    const [loading, setLoading] = useState(false);
    const isOutOfStock = product.track_inventory && product.stock <= 0;

    function handleAddToCart() {
        if (isOutOfStock) return;
        setLoading(true);
        router.post(
            '/cart/add',
            { id: product.id },
            {
                onSuccess: () => setLoading(false),
                onError: () => setLoading(false),
                preserveScroll: true,
            },
        );
    }

    return (
        <div
            className={`group relative flex flex-col overflow-hidden rounded-lg border border-sidebar-border/60 bg-background shadow-sm transition hover:shadow-md dark:border-sidebar-border ${isOutOfStock ? 'opacity-60' : ''}`}
        >
            {isOutOfStock && (
                <div className="absolute top-2 right-2 z-10">
                    <Badge variant="destructive">Out of Stock</Badge>
                </div>
            )}
            {product.track_inventory &&
                product.stock > 0 &&
                product.stock <= 10 && (
                    <div className="absolute top-2 right-2 z-10">
                        <Badge variant="secondary">
                            Only {product.stock} left
                        </Badge>
                    </div>
                )}
            {product.image_url ? (
                <img
                    src={product.image_url}
                    alt={product.name}
                    className="aspect-[4/3] w-full object-cover"
                />
            ) : (
                <div className="aspect-[4/3] w-full items-center justify-center bg-muted/40 text-muted-foreground">
                    <div className="flex h-full w-full items-center justify-center gap-2 text-sm">
                        <ImageIcon className="h-5 w-5" /> No image
                    </div>
                </div>
            )}
            <div className="flex flex-1 flex-col gap-2 p-3">
                <div className="flex items-start justify-between gap-2">
                    <div className="min-w-0">
                        <div className="truncate text-sm font-medium">
                            {product.name}
                        </div>
                        <div className="text-xs text-muted-foreground">
                            {product.category_name ?? 'Uncategorized'}
                        </div>
                        {product.sku && (
                            <div className="font-mono text-xs text-muted-foreground">
                                SKU: {product.sku}
                            </div>
                        )}
                    </div>
                    <div className="shrink-0 rounded bg-muted px-2 py-0.5 text-xs font-semibold">
                        ₱{Number(product.price).toFixed(2)}
                    </div>
                </div>
                {product.description && (
                    <div className="line-clamp-3 text-xs text-muted-foreground">
                        {product.description}
                    </div>
                )}
                <div className="mt-auto flex items-center justify-end gap-2 pt-1">
                    <button
                        onClick={handleAddToCart}
                        disabled={loading || isOutOfStock}
                        className={`inline-flex w-full items-center justify-center gap-0 rounded px-3 py-2 text-sm ${
                            isOutOfStock
                                ? 'cursor-not-allowed bg-muted text-muted-foreground'
                                : 'bg-primary text-primary-foreground hover:bg-primary/90'
                        }`}
                    >
                        {loading
                            ? 'Adding...'
                            : isOutOfStock
                              ? 'Out of Stock'
                              : 'Select Item'}
                    </button>
                </div>
            </div>
        </div>
    );
}

function BarcodeScanner({
    open,
    onClose,
}: {
    open: boolean;
    onClose: () => void;
}) {
    const [barcode, setBarcode] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');
    const inputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (open && inputRef.current) {
            inputRef.current.focus();
        }
    }, [open]);

    useEffect(() => {
        if (success) {
            const timer = setTimeout(() => setSuccess(''), 2000);
            return () => clearTimeout(timer);
        }
    }, [success]);

    const handleScan = async () => {
        if (!barcode.trim() || loading) return;

        setLoading(true);
        setError('');
        setSuccess('');

        try {
            const response = await fetch('/cart/add-barcode', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',
                },
                body: JSON.stringify({ barcode: barcode.trim() }),
            });

            const data = await response.json();

            if (data.success) {
                setSuccess(data.message);
                setBarcode('');
                // Refresh page to get updated cart count
                router.reload({ only: ['cart'] });
            } else {
                setError(data.message);
            }
        } catch {
            setError('Failed to add product');
        }

        setLoading(false);
        inputRef.current?.focus();
    };

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <Barcode className="h-5 w-5" />
                        Barcode Scanner
                    </DialogTitle>
                    <DialogDescription>
                        Scan a barcode or enter SKU to add product to cart
                    </DialogDescription>
                </DialogHeader>
                <div className="space-y-4">
                    <div className="flex gap-2">
                        <Input
                            ref={inputRef}
                            placeholder="Scan barcode or enter SKU..."
                            value={barcode}
                            onChange={(e) => setBarcode(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && handleScan()}
                            autoFocus
                        />
                        <Button
                            onClick={handleScan}
                            disabled={loading || !barcode.trim()}
                        >
                            {loading ? 'Adding...' : 'Add'}
                        </Button>
                    </div>
                    {error && (
                        <div className="flex items-center gap-2 rounded-lg bg-red-50 p-3 text-sm text-red-600 dark:bg-red-900/20">
                            <AlertCircle className="h-4 w-4" />
                            {error}
                        </div>
                    )}
                    {success && (
                        <div className="flex items-center gap-2 rounded-lg bg-green-50 p-3 text-sm text-green-600 dark:bg-green-900/20">
                            <Package className="h-4 w-4" />
                            {success}
                        </div>
                    )}
                    <div className="text-center text-xs text-muted-foreground">
                        Tip: Keep this open and use a barcode scanner. Products
                        will be added automatically.
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}

export default function MenuIndex() {
    const { props } = usePage<MenuPageProps>();
    const products = useMemo(() => props.products ?? [], [props.products]);
    const cartCount = props.cart?.count ?? 0;
    const categories = props.categories ?? [];
    const { search, setSearch, category, setCategory } = useQuerySync(
        props.filters ?? {},
    );
    const [cartOpen, setCartOpen] = useState(false);
    const [scannerOpen, setScannerOpen] = useState(false);
    const searchInputRef = useRef<HTMLInputElement>(null);

    // Keyboard shortcuts
    const handleKeyPress = useCallback(
        (e: KeyboardEvent) => {
            // Don't trigger shortcuts when typing in inputs
            if (
                e.target instanceof HTMLInputElement ||
                e.target instanceof HTMLTextAreaElement
            ) {
                if (e.key === 'Escape') {
                    setCartOpen(false);
                    setScannerOpen(false);
                }
                return;
            }

            // Alt + B to open barcode scanner
            if (e.altKey && e.key.toLowerCase() === 'b') {
                e.preventDefault();
                setScannerOpen(true);
            }

            // Alt + C to open cart
            if (e.altKey && e.key.toLowerCase() === 'c') {
                e.preventDefault();
                setCartOpen(true);
            }

            // Alt + S or / to focus search
            if ((e.altKey && e.key.toLowerCase() === 's') || e.key === '/') {
                e.preventDefault();
                searchInputRef.current?.focus();
            }

            // Escape to close modals
            if (e.key === 'Escape') {
                setCartOpen(false);
                setScannerOpen(false);
            }

            // Alt + number to quick add products (1-9)
            if (e.altKey && !e.ctrlKey && !e.shiftKey) {
                const num = parseInt(e.key);
                if (num >= 1 && num <= 9 && products[num - 1]) {
                    e.preventDefault();
                    const product = products[num - 1];
                    router.post(
                        '/cart/add',
                        { id: product.id },
                        { preserveScroll: true },
                    );
                }
            }
        },
        [products],
    );

    useEffect(() => {
        window.addEventListener('keydown', handleKeyPress);
        return () => window.removeEventListener('keydown', handleKeyPress);
    }, [handleKeyPress]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Menu" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-col gap-2 rounded-lg border border-sidebar-border/60 p-3 md:flex-row md:items-center md:justify-between dark:border-sidebar-border">
                    <div className="flex w-full items-center gap-2 md:w-auto">
                        <div className="relative w-full md:w-80">
                            <Search className="pointer-events-none absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                            <input
                                ref={searchInputRef}
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search products... (/ or Alt+S)"
                                className="h-9 w-full rounded border border-input bg-background pr-3 pl-8 text-sm"
                            />
                        </div>
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() => setScannerOpen(true)}
                            title="Barcode Scanner (Alt+B)"
                        >
                            <Barcode className="h-4 w-4" />
                        </Button>
                    </div>
                    <div className="flex items-center gap-2">
                        <select
                            value={category}
                            onChange={(e) => setCategory(e.target.value)}
                            className="h-9 rounded border border-input bg-background px-3 text-sm"
                        >
                            <option value="">All categories</option>
                            {categories.map((c) => (
                                <option key={c.slug} value={c.slug}>
                                    {c.name}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>

                {/* Keyboard shortcuts hint */}
                <div className="flex flex-wrap items-center justify-center gap-4 text-xs text-muted-foreground">
                    <div className="flex items-center gap-1">
                        <Keyboard className="h-3 w-3" />
                        <span>Shortcuts:</span>
                    </div>
                    <span>Alt+B = Scanner</span>
                    <span>Alt+C = Cart</span>
                    <span>Alt+1-9 = Quick Add</span>
                    <span>/ = Search</span>
                </div>

                {products.length === 0 ? (
                    <div className="text-sm text-muted-foreground">
                        No products found.
                    </div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {products.map((p, index) => (
                            <div key={p.id} className="relative">
                                {index < 9 && (
                                    <Badge
                                        variant="outline"
                                        className="absolute -top-2 -left-2 z-10 bg-background text-xs"
                                    >
                                        Alt+{index + 1}
                                    </Badge>
                                )}
                                <ProductCard product={p} />
                            </div>
                        ))}
                    </div>
                )}

                <button
                    className="fixed right-6 bottom-6 z-50 flex items-center gap-2 rounded-full bg-primary px-4 py-2 text-primary-foreground shadow-lg"
                    onClick={() => setCartOpen(true)}
                >
                    <ShoppingBag className="h-5 w-5" />
                    Cart
                    {cartCount > 0 && (
                        <span className="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-white text-xs font-semibold text-primary">
                            {cartCount}
                        </span>
                    )}
                </button>

                <CartSidebar open={cartOpen} onOpenChange={setCartOpen} />
                <BarcodeScanner
                    open={scannerOpen}
                    onClose={() => setScannerOpen(false)}
                />
            </div>
        </AppLayout>
    );
}
