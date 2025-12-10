import CartSidebar from '@/components/cart-sidebar';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import type { MenuPageProps, MenuProductCardProps } from '@/types/menu.d';
import { Head, router, usePage } from '@inertiajs/react';
import { Image as ImageIcon, Search, ShoppingBag } from 'lucide-react';
import { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Menu', href: '/menu' }];

function useQuerySync(initial: { search?: string; category?: string }) {
    const [search, setSearch] = useState(initial.search ?? '');
    // category is now the taxonomy slug
    const [category, setCategory] = useState(initial.category ?? '');

    useEffect(() => {
        const t = setTimeout(() => {
            // Build query string from search and category
            const params: Record<string, string> = {};
            if (search) params.search = search;
            // Use taxonomy slug for category
            if (category) params.category = category;
            const queryString = new URLSearchParams(params).toString();
            // Use pathname only, not full href
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

function ProductCard({ product, onAddToCart }: MenuProductCardProps) {
    const [loading, setLoading] = useState(false);

    function handleAddToCart() {
        setLoading(true);
        router.post(
            '/cart/add',
            { id: product.id },
            {
                onSuccess: () => {
                    setLoading(false);
                    if (onAddToCart) onAddToCart();
                },
                onError: () => setLoading(false),
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }

    return (
        <div className="group relative flex flex-col overflow-hidden rounded-lg border border-sidebar-border/60 bg-background shadow-sm transition hover:shadow-md dark:border-sidebar-border">
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
                <>
                    <div className="flex items-start justify-between gap-2">
                        <div className="min-w-0">
                            <div className="truncate text-sm font-medium">
                                {product.name}
                            </div>
                            <div className="text-xs text-muted-foreground">
                                {product.category_name ?? 'Uncategorized'}
                            </div>
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
                            disabled={loading}
                            className="inline-flex w-full items-center justify-center gap-0 rounded bg-primary px-3 py-2 text-sm text-primary-foreground"
                        >
                            {loading ? 'Adding...' : 'Select Item'}
                        </button>
                    </div>
                </>
            </div>
        </div>
    );
}

export default function MenuIndex() {
    const { props } = usePage<MenuPageProps>();
    const products = props.products ?? [];
    const initialCartCount = props.cart?.count ?? 0;
    const categories = props.categories ?? [];
    const { search, setSearch, category, setCategory } = useQuerySync(
        props.filters ?? {},
    );
    const [cartOpen, setCartOpen] = useState(false);
    const [cartCount, setCartCount] = useState(initialCartCount);

    useEffect(() => {
        setCartCount(initialCartCount);
    }, [initialCartCount]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Menu" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-col gap-2 rounded-lg border border-sidebar-border/60 p-3 md:flex-row md:items-center md:justify-between dark:border-sidebar-border">
                    <div className="flex w-full items-center gap-2 md:w-auto">
                        <div className="relative w-full md:w-80">
                            <Search className="pointer-events-none absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                            <input
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search products..."
                                className="h-9 w-full rounded border border-input bg-background pr-3 pl-8 text-sm"
                            />
                        </div>
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

                {products.length === 0 ? (
                    <div className="text-sm text-muted-foreground">
                        No products found.
                    </div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {products.map((p) => (
                            <ProductCard
                                key={p.id}
                                product={p}
                                onAddToCart={() => setCartCount((c) => c + 1)}
                            />
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
            </div>
        </AppLayout>
    );
}
