import AlertDialog from '@/components/alert-dialog';
import AppLayout from '@/layouts/app-layout';
import {
    destroy as productsDestroy,
    index as productsIndex,
    store as productsStore,
    update as productsUpdate,
} from '@/routes/products';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    Image as ImageIcon,
    Pencil,
    Plus,
    Search,
    Trash2,
    X,
} from 'lucide-react';
import React, { useEffect, useMemo, useState } from 'react';
// Types coming from the controller payload
export type Product = {
    id: number;
    name: string;
    description?: string | null;
    price: string | number;
    image_url?: string | null;
    category_parent_id?: string | null;
    category_id?: string | null;
    category_name?: string | null;
};

export type CategoryOption = { id: string; name: string; slug: string };

interface PageProps {
    products: Product[];
    categories: CategoryOption[];
    filters: { search?: string; category?: string };
}

function useQuerySync(initial: { search?: string; category?: string }) {
    const [search, setSearch] = useState(initial.search ?? '');
    const [category, setCategory] = useState(initial.category ?? '');

    // Submit query when user stops typing or changes category
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

function CreateProductModal({
    open,
    onClose,
    categories,
    onOptimisticCreate,
}: {
    open: boolean;
    onClose: () => void;
    categories: CategoryOption[];
    onOptimisticCreate?: (p: Product) => void;
}) {
    const form = useForm<{
        name: string;
        description: string;
        price: number | string;
        category_id: string | '';
        image: File | null;
    }>({
        name: '',
        description: '',
        price: '',
        category_id: '',
        image: null,
    });

    const [confirmCreateOpen, setConfirmCreateOpen] = useState(false);
    const [confirmCancelOpen, setConfirmCancelOpen] = useState(false);

    const doCreate = () => {
        form.post(productsStore().url, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onClose();
            },
        });
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        setConfirmCreateOpen(true);
    };

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div className="w-full max-w-lg rounded-lg border border-sidebar-border/60 bg-background p-4 shadow-xl dark:border-sidebar-border">
                <div className="mb-3 flex items-center justify-between">
                    <h2 className="text-lg font-semibold">Add product</h2>
                    <button
                        onClick={() => setConfirmCancelOpen(true)}
                        className="rounded p-1 hover:bg-muted"
                        aria-label="Close"
                    >
                        <X className="h-5 w-5" />
                    </button>
                </div>
                <form onSubmit={submit} className="space-y-3">
                    <div className="grid gap-2">
                        <label className="text-sm">Name</label>
                        <input
                            className="h-9 rounded border border-input bg-background px-3 text-sm"
                            value={form.data.name}
                            onChange={(e) =>
                                form.setData('name', e.target.value)
                            }
                            required
                        />
                        {form.errors.name && (
                            <p className="text-xs text-red-600">
                                {form.errors.name}
                            </p>
                        )}
                    </div>
                    <div className="grid gap-2">
                        <label className="text-sm">Description</label>
                        <textarea
                            className="min-h-24 rounded border border-input bg-background px-3 py-2 text-sm"
                            value={form.data.description}
                            onChange={(e) =>
                                form.setData('description', e.target.value)
                            }
                        />
                        {form.errors.description && (
                            <p className="text-xs text-red-600">
                                {form.errors.description}
                            </p>
                        )}
                    </div>
                    <div className="grid gap-2">
                        <label className="text-sm">Price</label>
                        <input
                            type="number"
                            step="0.01"
                            className="h-9 rounded border border-input bg-background px-3 text-sm"
                            value={form.data.price}
                            onChange={(e) =>
                                form.setData('price', e.target.value)
                            }
                            required
                        />
                        {form.errors.price && (
                            <p className="text-xs text-red-600">
                                {form.errors.price}
                            </p>
                        )}
                    </div>
                    <div className="grid gap-2">
                        <label className="text-sm">Category</label>
                        <select
                            className="h-9 rounded border border-input bg-background px-3 text-sm"
                            value={form.data.category_id}
                            onChange={(e) =>
                                form.setData('category_id', e.target.value)
                            }
                        >
                            <option value="">— None —</option>
                            {categories.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.name}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="grid gap-2">
                        <label className="text-sm">
                            Image (jpeg, jpg, png)
                        </label>
                        <input
                            accept="image/jpeg,image/png,.jpg,.jpeg,.png"
                            type="file"
                            onChange={(e) =>
                                form.setData(
                                    'image',
                                    e.target.files?.[0] ?? null,
                                )
                            }
                        />
                        {form.errors.image && (
                            <p className="text-xs text-red-600">
                                {form.errors.image}
                            </p>
                        )}
                    </div>

                    <div className="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="button"
                            onClick={() => setConfirmCancelOpen(true)}
                            className="h-9 rounded border px-3 text-sm"
                        >
                            Cancel
                        </button>
                        <button
                            className="h-9 rounded bg-primary px-3 text-sm text-primary-foreground disabled:opacity-50"
                            disabled={form.processing}
                        >
                            Create
                        </button>
                    </div>
                    <AlertDialog
                        open={confirmCreateOpen}
                        title="Create product?"
                        description="Are you sure you want to create this product?"
                        confirmLabel="Create"
                        cancelLabel="Back"
                        onCancel={() => setConfirmCreateOpen(false)}
                        onConfirm={() => {
                            setConfirmCreateOpen(false);
                            // optimistic insert
                            try {
                                const tempId = -Date.now();
                                const catName =
                                    categories.find(
                                        (c) => c.id === form.data.category_id,
                                    )?.name ?? null;
                                const tempProduct: Product = {
                                    id: tempId,
                                    name: form.data.name,
                                    description: form.data.description || null,
                                    price: form.data.price,
                                    image_url: null,
                                    category_parent_id: null,
                                    category_id: form.data.category_id || null,
                                    category_name: catName,
                                };
                                onOptimisticCreate?.(tempProduct);
                            } catch (e) {
                                void e;
                            }
                            doCreate();
                        }}
                    />
                    <AlertDialog
                        open={confirmCancelOpen}
                        title="Discard changes?"
                        description="Discard the changes?"
                        confirmLabel="Discard"
                        cancelLabel="Keep editing"
                        onCancel={() => setConfirmCancelOpen(false)}
                        onConfirm={() => {
                            setConfirmCancelOpen(false);
                            form.reset();
                            onClose();
                        }}
                    />
                </form>
            </div>
        </div>
    );
}

function ProductCard({
    product,
    categories,
    onOptimisticUpdate,
    onOptimisticDelete,
}: {
    product: Product;
    categories: CategoryOption[];
    onOptimisticUpdate?: (p: Product) => void;
    onOptimisticDelete?: (id: number) => void;
}) {
    const [editing, setEditing] = useState(false);
    const [confirmSaveOpen, setConfirmSaveOpen] = useState(false);
    const [confirmCancelOpen, setConfirmCancelOpen] = useState(false);
    const [confirmDeleteOpen, setConfirmDeleteOpen] = useState(false);
    const form = useForm<{
        name: string;
        description: string;
        price: number | string;
        category_id: string | '';
        image: File | null;
    }>({
        name: product.name,
        description: product.description ?? '',
        price: product.price,
        category_id: product.category_id ?? '',
        image: null,
    });
    const onDelete = () => {
        setConfirmDeleteOpen(true);
    };

    const doUpdate = () => {
        // optimistic update
        try {
            const catName =
                categories.find((c) => c.slug === form.data.category_id)
                    ?.name ?? null;
            const optimistic: Product = {
                ...product,
                name: form.data.name,
                description: form.data.description || null,
                price: form.data.price,
                category_id: form.data.category_id || null,
                category_name: catName,
            };
            onOptimisticUpdate?.(optimistic);
        } catch (e) {
            void e;
        }

        form.post(productsUpdate(product.id).url, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setEditing(false);
            },
        });
    };

    const doDelete = () => {
        // optimistic remove
        try {
            onOptimisticDelete?.(product.id);
        } catch (e) {
            void e;
        }

        form.delete(productsDestroy(product.id).url, {
            preserveScroll: true,
        });
    };

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
                {editing ? (
                    <form className="flex flex-col gap-2">
                        <input
                            className="h-8 rounded border border-input bg-background px-2 text-sm"
                            value={form.data.name}
                            onChange={(e) =>
                                form.setData('name', e.target.value)
                            }
                            required
                        />
                        <textarea
                            className="min-h-20 rounded border border-input bg-background px-2 py-1 text-sm"
                            value={form.data.description}
                            onChange={(e) =>
                                form.setData('description', e.target.value)
                            }
                        />
                        <div className="grid grid-cols-2 gap-2">
                            <input
                                type="number"
                                step="0.01"
                                className="h-8 rounded border border-input bg-background px-2 text-sm"
                                value={form.data.price}
                                onChange={(e) =>
                                    form.setData('price', e.target.value)
                                }
                                required
                            />
                            <select
                                className="h-8 rounded border border-input bg-background px-2 text-sm"
                                value={form.data.category_id}
                                onChange={(e) =>
                                    form.setData('category_id', e.target.value)
                                }
                            >
                                <option value="">— None —</option>
                                {categories.map((c) => (
                                    <option key={c.id} value={c.slug}>
                                        {c.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <input
                            accept="image/jpeg,image/png,.jpg,.jpeg,.png"
                            type="file"
                            onChange={(e) =>
                                form.setData(
                                    'image',
                                    e.target.files?.[0] ?? null,
                                )
                            }
                        />
                        {Object.values(form.errors).length > 0 && (
                            <div className="text-xs text-red-600">
                                {Object.values(form.errors)[0]}
                            </div>
                        )}
                        <div className="mt-1 flex items-center gap-2">
                            <button
                                type="button"
                                onClick={() => setConfirmSaveOpen(true)}
                                className="h-8 rounded bg-primary px-3 text-xs text-primary-foreground disabled:opacity-50"
                                disabled={form.processing}
                            >
                                Save
                            </button>
                            <button
                                type="button"
                                onClick={() => setConfirmCancelOpen(true)}
                                className="h-8 rounded border px-3 text-xs"
                            >
                                Cancel
                            </button>
                        </div>
                        <AlertDialog
                            open={confirmSaveOpen}
                            title="Save changes?"
                            description="Apply these changes to the product?"
                            confirmLabel="Save"
                            cancelLabel="Back"
                            onCancel={() => setConfirmSaveOpen(false)}
                            onConfirm={() => {
                                setConfirmSaveOpen(false);
                                doUpdate();
                            }}
                        />
                        <AlertDialog
                            open={confirmCancelOpen}
                            title="Discard changes?"
                            description="Discard the changes?"
                            confirmLabel="Discard"
                            cancelLabel="Keep editing"
                            onCancel={() => setConfirmCancelOpen(false)}
                            onConfirm={() => {
                                setConfirmCancelOpen(false);
                                form.reset();
                                setEditing(false);
                            }}
                        />
                    </form>
                ) : (
                    <>
                        <div className="flex items-start justify-between gap-2">
                            <div className="min-w-0">
                                <div className="truncate text-sm font-medium">
                                    {product.name}
                                </div>
                                <div className="text-xs text-muted-foreground">
                                    {product.category_name ?? 'Uncategorized'}
                                </div>
                                <AlertDialog
                                    open={confirmDeleteOpen}
                                    title="Delete product?"
                                    description="This action cannot be undone. Delete the product?"
                                    confirmLabel="Delete"
                                    cancelLabel="Cancel"
                                    destructive
                                    onCancel={() => setConfirmDeleteOpen(false)}
                                    onConfirm={() => {
                                        setConfirmDeleteOpen(false);
                                        doDelete();
                                    }}
                                />
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
                                onClick={() => setEditing(true)}
                                className="inline-flex items-center gap-1 rounded border px-2 py-1 text-xs"
                            >
                                <Pencil className="h-3 w-3" /> Edit
                            </button>
                            <button
                                onClick={onDelete}
                                className="inline-flex items-center gap-1 rounded border px-2 py-1 text-xs text-red-600"
                            >
                                <Trash2 className="h-3 w-3" /> Delete
                            </button>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
}

export default function ProductsIndex() {
    const { props } = usePage<PageProps>();
    const [localProducts, setLocalProducts] = useState<Product[]>(
        () => props.products ?? [],
    );
    useEffect(() => {
        const next = props.products ?? [];
        const same =
            localProducts.length === next.length &&
            localProducts.every((p, i) => p.id === next[i].id);
        if (!same) setLocalProducts(next);
    }, [props.products, localProducts]);
    const categories = props.categories ?? [];
    const { search, setSearch, category, setCategory } = useQuerySync(
        props.filters ?? {},
    );
    const [openCreate, setOpenCreate] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = useMemo(
        () => [{ title: 'Products', href: productsIndex().url }],
        [],
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Products" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between gap-2">
                    <div>
                        <h1 className="text-xl font-semibold">Products</h1>
                        <p className="text-sm text-muted-foreground">
                            Browse and manage your food products.
                        </p>
                    </div>
                    <button
                        onClick={() => setOpenCreate(true)}
                        className="inline-flex items-center gap-1 rounded bg-primary px-3 py-2 text-sm text-primary-foreground"
                    >
                        <Plus className="h-4 w-4" /> Add product
                    </button>
                </div>

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
                                <option key={c.id} value={c.slug}>
                                    {c.name}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>

                {localProducts.length === 0 ? (
                    <div className="text-sm text-muted-foreground">
                        No products found.
                    </div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {localProducts.map((p) => (
                            <ProductCard
                                key={p.id}
                                product={p}
                                categories={categories}
                                onOptimisticUpdate={(prod) =>
                                    setLocalProducts((prev) =>
                                        prev.map((x) =>
                                            x.id === prod.id
                                                ? { ...x, ...prod }
                                                : x,
                                        ),
                                    )
                                }
                                onOptimisticDelete={(id) =>
                                    setLocalProducts((prev) =>
                                        prev.filter((x) => x.id !== id),
                                    )
                                }
                            />
                        ))}
                    </div>
                )}
            </div>

            <CreateProductModal
                open={openCreate}
                onClose={() => setOpenCreate(false)}
                categories={categories}
                onOptimisticCreate={(p) =>
                    setLocalProducts((prev) => [p, ...prev])
                }
            />
        </AppLayout>
    );
}
