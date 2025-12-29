import AlertDialog from '@/components/alert-dialog';
import AppLayout from '@/layouts/app-layout';
import {
    destroy as productsDestroy,
    index as productsIndex,
    store as productsStore,
    update as productsUpdate,
} from '@/routes/products';
import { type BreadcrumbItem } from '@/types';
import type { CategoryOption } from '@/types/category';
import type {
    CreateProductModalProps,
    Product,
    ProductCardProps,
    ProductFormData,
    ProductPageProps,
} from '@/types/products.d';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import axios from 'axios';
import {
    AlertCircle,
    ChevronDown,
    ChevronUp,
    Image as ImageIcon,
    Package,
    Pencil,
    Plus,
    RefreshCw,
    Search,
    Trash2,
    X,
} from 'lucide-react';
import { type FormEvent, useCallback, useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Products', href: productsIndex().url },
];

const VAT_RATE = 0.12; // 12% Philippine VAT

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

function getDefaultFormData(): ProductFormData {
    return {
        name: '',
        description: '',
        sku: '',
        barcode: '',
        price: '',
        discount: '',
        discount_to: '',
        vat: '',
        has_vat: true,
        stock: '',
        track_inventory: false,
        is_activated: true,
        has_unlimited_stock: false,
        has_max_cart: false,
        min_cart: '',
        max_cart: '',
        has_stock_alert: false,
        min_stock_alert: '',
        max_stock_alert: '',
        category_id: '',
        image: null,
    };
}

function getFormDataFromProduct(product: Product): ProductFormData {
    return {
        name: product.name,
        description: product.description ?? '',
        sku: product.sku ?? '',
        barcode: product.barcode ?? '',
        price: product.price,
        discount: product.discount ?? '',
        discount_to: product.discount_to ?? '',
        vat: product.vat ?? '',
        has_vat: product.has_vat !== false,
        stock: product.stock ?? '',
        track_inventory: Boolean(product.track_inventory),
        is_activated: product.is_activated !== false,
        has_unlimited_stock: Boolean(product.has_unlimited_stock),
        has_max_cart: Boolean(product.has_max_cart),
        min_cart: product.min_cart ?? '',
        max_cart: product.max_cart ?? '',
        has_stock_alert: Boolean(product.has_stock_alert),
        min_stock_alert: product.min_stock_alert ?? '',
        max_stock_alert: product.max_stock_alert ?? '',
        category_id: Array.isArray(product.category_id)
            ? (product.category_id[0] ?? '')
            : (product.category_id ?? ''),
        image: null,
    };
}

// Calculate VAT from VAT-inclusive price (12% Philippine VAT)
// For VAT-inclusive: VAT = Price × (12/112), Vatable Sales = Price / 1.12
function calculateVatInclusive(
    price: number,
    hasVat: boolean = true,
): { vatAmount: number; vatableSales: number } {
    if (!hasVat) {
        return { vatAmount: 0, vatableSales: price };
    }
    const vatAmount = price * (VAT_RATE / (1 + VAT_RATE)); // VAT = Price × (12/112)
    const vatableSales = price / (1 + VAT_RATE); // Vatable Sales = Price / 1.12
    return { vatAmount, vatableSales };
}

interface CollapsibleSectionProps {
    title: string;
    icon?: React.ReactNode;
    defaultOpen?: boolean;
    children: React.ReactNode;
}

function CollapsibleSection({
    title,
    icon,
    defaultOpen = false,
    children,
}: CollapsibleSectionProps) {
    const [open, setOpen] = useState(defaultOpen);
    return (
        <div className="rounded-lg border border-sidebar-border/60 dark:border-sidebar-border">
            <button
                type="button"
                onClick={() => setOpen(!open)}
                className="flex w-full items-center justify-between px-3 py-2 text-sm font-medium hover:bg-muted/50"
            >
                <span className="flex items-center gap-2">
                    {icon}
                    {title}
                </span>
                {open ? (
                    <ChevronUp className="h-4 w-4" />
                ) : (
                    <ChevronDown className="h-4 w-4" />
                )}
            </button>
            {open && (
                <div className="border-t border-sidebar-border/60 p-3 dark:border-sidebar-border">
                    {children}
                </div>
            )}
        </div>
    );
}

interface ProductFormFieldsProps {
    form: ReturnType<typeof useForm<ProductFormData>>;
    categories: CategoryOption[];
    onGenerateCodes?: () => void;
    isGenerating?: boolean;
}

function ProductFormFields({
    form,
    categories,
    onGenerateCodes,
    isGenerating,
}: ProductFormFieldsProps) {
    const price = Number(form.data.price) || 0;
    const discount = Number(form.data.discount) || 0;
    const hasVat = form.data.has_vat;
    const discountedPrice = discount > 0 ? price * (1 - discount / 100) : price;
    // VAT-inclusive calculation
    const { vatAmount, vatableSales } = calculateVatInclusive(
        discountedPrice,
        hasVat,
    );

    return (
        <div className="space-y-4">
            {/* Basic Info */}
            <div className="grid gap-3">
                <div className="grid gap-2">
                    <label className="text-sm font-medium">Name *</label>
                    <input
                        className="h-9 rounded border border-input bg-background px-3 text-sm"
                        value={form.data.name}
                        onChange={(e) => form.setData('name', e.target.value)}
                        required
                    />
                    {form.errors.name && (
                        <p className="text-xs text-red-600">
                            {form.errors.name}
                        </p>
                    )}
                </div>
                <div className="grid gap-2">
                    <label className="text-sm font-medium">Description</label>
                    <textarea
                        className="min-h-20 rounded border border-input bg-background px-3 py-2 text-sm"
                        value={form.data.description}
                        onChange={(e) =>
                            form.setData('description', e.target.value)
                        }
                    />
                </div>
                <div className="grid gap-2">
                    <label className="text-sm font-medium">Category</label>
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
                <div className="grid grid-cols-2 gap-3">
                    <div className="grid gap-2">
                        <label className="text-sm font-medium">SKU</label>
                        <input
                            className="h-9 rounded border border-input bg-background px-3 text-sm"
                            value={form.data.sku}
                            onChange={(e) =>
                                form.setData('sku', e.target.value)
                            }
                            placeholder="Auto-generated"
                        />
                    </div>
                    <div className="grid gap-2">
                        <label className="text-sm font-medium">
                            Barcode (UUID)
                        </label>
                        <input
                            className="h-9 rounded border border-input bg-background px-3 font-mono text-sm text-xs"
                            value={form.data.barcode}
                            onChange={(e) =>
                                form.setData('barcode', e.target.value)
                            }
                            placeholder="Auto-generated"
                        />
                    </div>
                </div>
                {onGenerateCodes && (
                    <button
                        type="button"
                        onClick={onGenerateCodes}
                        disabled={isGenerating}
                        className="inline-flex items-center gap-2 text-sm text-primary hover:underline disabled:opacity-50"
                    >
                        <RefreshCw
                            className={`h-4 w-4 ${isGenerating ? 'animate-spin' : ''}`}
                        />
                        {isGenerating
                            ? 'Generating...'
                            : 'Generate SKU & Barcode'}
                    </button>
                )}
            </div>

            {/* Pricing */}
            <CollapsibleSection title="Pricing" defaultOpen={true}>
                <div className="grid gap-3">
                    <div className="grid grid-cols-2 gap-3">
                        <div className="grid gap-2">
                            <label className="text-sm font-medium">
                                Selling Price {hasVat ? '(VAT-inclusive)' : ''}{' '}
                                *
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
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
                            <label className="text-sm font-medium">
                                Discount %
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                className="h-9 rounded border border-input bg-background px-3 text-sm"
                                value={form.data.discount}
                                onChange={(e) =>
                                    form.setData('discount', e.target.value)
                                }
                                placeholder="0"
                            />
                        </div>
                    </div>
                    <div className="grid gap-2">
                        <label className="text-sm font-medium">
                            Discount Until
                        </label>
                        <input
                            type="datetime-local"
                            className="h-9 rounded border border-input bg-background px-3 text-sm"
                            value={form.data.discount_to}
                            onChange={(e) =>
                                form.setData('discount_to', e.target.value)
                            }
                        />
                    </div>
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={form.data.has_vat}
                            onChange={(e) =>
                                form.setData('has_vat', e.target.checked)
                            }
                            className="h-4 w-4 rounded border-gray-300"
                        />
                        Include VAT (12%)
                    </label>
                    {price > 0 && (
                        <div className="rounded-lg bg-muted/50 p-3 text-sm">
                            <div className="mb-2 font-medium">
                                VAT-Inclusive Price Breakdown
                            </div>
                            <div className="space-y-1 text-muted-foreground">
                                <div className="flex justify-between font-semibold text-foreground">
                                    <span>Selling Price:</span>
                                    <span>₱{price.toFixed(2)}</span>
                                </div>
                                {discount > 0 && (
                                    <div className="flex justify-between text-green-600">
                                        <span>After {discount}% Discount:</span>
                                        <span>
                                            ₱{discountedPrice.toFixed(2)}
                                        </span>
                                    </div>
                                )}
                                <div className="mt-1 border-t pt-1">
                                    {hasVat ? (
                                        <>
                                            <div className="flex justify-between">
                                                <span>Vatable Sales:</span>
                                                <span>
                                                    ₱{vatableSales.toFixed(2)}
                                                </span>
                                            </div>
                                            <div className="flex justify-between">
                                                <span>VAT (12%):</span>
                                                <span>
                                                    ₱{vatAmount.toFixed(2)}
                                                </span>
                                            </div>
                                        </>
                                    ) : (
                                        <div className="flex justify-between">
                                            <span>VAT Exempt</span>
                                            <span>₱0.00</span>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </CollapsibleSection>

            {/* Inventory */}
            <CollapsibleSection
                title="Inventory"
                icon={<Package className="h-4 w-4" />}
            >
                <div className="grid gap-3">
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={form.data.track_inventory}
                            onChange={(e) =>
                                form.setData(
                                    'track_inventory',
                                    e.target.checked,
                                )
                            }
                            className="h-4 w-4 rounded border-gray-300"
                        />
                        Track inventory
                    </label>
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={form.data.has_unlimited_stock}
                            onChange={(e) =>
                                form.setData(
                                    'has_unlimited_stock',
                                    e.target.checked,
                                )
                            }
                            className="h-4 w-4 rounded border-gray-300"
                        />
                        Unlimited stock
                    </label>
                    {!form.data.has_unlimited_stock && (
                        <div className="grid gap-2">
                            <label className="text-sm font-medium">
                                Stock Quantity
                            </label>
                            <input
                                type="number"
                                min="0"
                                className="h-9 rounded border border-input bg-background px-3 text-sm"
                                value={form.data.stock}
                                onChange={(e) =>
                                    form.setData('stock', e.target.value)
                                }
                                placeholder="0"
                            />
                        </div>
                    )}
                </div>
            </CollapsibleSection>

            {/* Cart Limits */}
            <CollapsibleSection title="Cart Limits">
                <div className="grid gap-3">
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={form.data.has_max_cart}
                            onChange={(e) =>
                                form.setData('has_max_cart', e.target.checked)
                            }
                            className="h-4 w-4 rounded border-gray-300"
                        />
                        Enable cart quantity limits
                    </label>
                    {form.data.has_max_cart && (
                        <div className="grid grid-cols-2 gap-3">
                            <div className="grid gap-2">
                                <label className="text-sm font-medium">
                                    Min Quantity
                                </label>
                                <input
                                    type="number"
                                    min="1"
                                    className="h-9 rounded border border-input bg-background px-3 text-sm"
                                    value={form.data.min_cart}
                                    onChange={(e) =>
                                        form.setData('min_cart', e.target.value)
                                    }
                                    placeholder="1"
                                />
                            </div>
                            <div className="grid gap-2">
                                <label className="text-sm font-medium">
                                    Max Quantity
                                </label>
                                <input
                                    type="number"
                                    min="1"
                                    className="h-9 rounded border border-input bg-background px-3 text-sm"
                                    value={form.data.max_cart}
                                    onChange={(e) =>
                                        form.setData('max_cart', e.target.value)
                                    }
                                    placeholder="10"
                                />
                            </div>
                        </div>
                    )}
                </div>
            </CollapsibleSection>

            {/* Stock Alerts */}
            <CollapsibleSection
                title="Stock Alerts"
                icon={<AlertCircle className="h-4 w-4" />}
            >
                <div className="grid gap-3">
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={form.data.has_stock_alert}
                            onChange={(e) =>
                                form.setData(
                                    'has_stock_alert',
                                    e.target.checked,
                                )
                            }
                            className="h-4 w-4 rounded border-gray-300"
                        />
                        Enable stock alerts
                    </label>
                    {form.data.has_stock_alert && (
                        <div className="grid grid-cols-2 gap-3">
                            <div className="grid gap-2">
                                <label className="text-sm font-medium">
                                    Low Stock Alert
                                </label>
                                <input
                                    type="number"
                                    min="0"
                                    className="h-9 rounded border border-input bg-background px-3 text-sm"
                                    value={form.data.min_stock_alert}
                                    onChange={(e) =>
                                        form.setData(
                                            'min_stock_alert',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="5"
                                />
                            </div>
                            <div className="grid gap-2">
                                <label className="text-sm font-medium">
                                    High Stock Alert
                                </label>
                                <input
                                    type="number"
                                    min="0"
                                    className="h-9 rounded border border-input bg-background px-3 text-sm"
                                    value={form.data.max_stock_alert}
                                    onChange={(e) =>
                                        form.setData(
                                            'max_stock_alert',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="100"
                                />
                            </div>
                        </div>
                    )}
                </div>
            </CollapsibleSection>

            {/* Status & Image */}
            <div className="grid gap-3">
                <label className="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        checked={form.data.is_activated}
                        onChange={(e) =>
                            form.setData('is_activated', e.target.checked)
                        }
                        className="h-4 w-4 rounded border-gray-300"
                    />
                    Product is active
                </label>
                <div className="grid gap-2">
                    <label className="text-sm font-medium">
                        Image (jpeg, jpg, png)
                    </label>
                    <input
                        accept="image/jpeg,image/png,.jpg,.jpeg,.png"
                        type="file"
                        onChange={(e) =>
                            form.setData('image', e.target.files?.[0] ?? null)
                        }
                        className="text-sm"
                    />
                    {form.errors.image && (
                        <p className="text-xs text-red-600">
                            {form.errors.image}
                        </p>
                    )}
                </div>
            </div>
        </div>
    );
}

function CreateProductModal({
    open,
    onClose,
    categories,
}: CreateProductModalProps) {
    const form = useForm<ProductFormData>(getDefaultFormData());
    const [confirmCreateOpen, setConfirmCreateOpen] = useState(false);
    const [confirmCancelOpen, setConfirmCancelOpen] = useState(false);
    const [isGenerating, setIsGenerating] = useState(false);

    const generateCodes = useCallback(async () => {
        setIsGenerating(true);
        try {
            const response = await axios.post('/products/generate-codes', {
                category_id: form.data.category_id || null,
            });
            form.setData((prev) => ({
                ...prev,
                sku: response.data.sku,
                barcode: response.data.barcode,
            }));
        } catch (error) {
            console.error('Failed to generate codes:', error);
        } finally {
            setIsGenerating(false);
        }
    }, [form]);

    // Auto-generate codes when modal opens
    useEffect(() => {
        if (open) {
            generateCodes();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    // Regenerate SKU when category changes
    const categoryId = form.data.category_id;
    useEffect(() => {
        if (open && categoryId) {
            generateCodes();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [categoryId]);

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

    const submit = (e: FormEvent) => {
        e.preventDefault();
        setConfirmCreateOpen(true);
    };

    const handleClose = () => {
        form.reset();
        onClose();
    };

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div className="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg border border-sidebar-border/60 bg-background p-4 shadow-xl dark:border-sidebar-border">
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
                <form onSubmit={submit}>
                    <ProductFormFields
                        form={form}
                        categories={categories}
                        onGenerateCodes={generateCodes}
                        isGenerating={isGenerating}
                    />

                    <div className="mt-4 flex items-center justify-end gap-2 border-t pt-4">
                        <button
                            type="button"
                            onClick={() => setConfirmCancelOpen(true)}
                            className="h-9 rounded border px-3 text-sm"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
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
                            handleClose();
                        }}
                    />
                </form>
            </div>
        </div>
    );
}

interface EditProductModalProps {
    product: Product;
    categories: CategoryOption[];
    open: boolean;
    onClose: () => void;
}

function EditProductModal({
    product,
    categories,
    open,
    onClose,
}: EditProductModalProps) {
    const form = useForm<ProductFormData>(getFormDataFromProduct(product));
    const [confirmSaveOpen, setConfirmSaveOpen] = useState(false);
    const [confirmCancelOpen, setConfirmCancelOpen] = useState(false);

    // Reset form when product changes
    const productId = product.id;
    useEffect(() => {
        if (open) {
            form.setData(getFormDataFromProduct(product));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [productId, open]);

    const doUpdate = () => {
        form.post(productsUpdate(product.id).url, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                onClose();
            },
            // Use _method for PUT with form data
            data: {
                ...form.data,
                _method: 'PUT',
            },
        });
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        setConfirmSaveOpen(true);
    };

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div className="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg border border-sidebar-border/60 bg-background p-4 shadow-xl dark:border-sidebar-border">
                <div className="mb-3 flex items-center justify-between">
                    <h2 className="text-lg font-semibold">Edit product</h2>
                    <button
                        onClick={() => setConfirmCancelOpen(true)}
                        className="rounded p-1 hover:bg-muted"
                        aria-label="Close"
                    >
                        <X className="h-5 w-5" />
                    </button>
                </div>
                <form onSubmit={submit}>
                    {product.image_url && (
                        <div className="mb-4">
                            <img
                                src={product.image_url}
                                alt={product.name}
                                className="h-32 w-32 rounded-lg object-cover"
                            />
                        </div>
                    )}
                    <ProductFormFields form={form} categories={categories} />

                    <div className="mt-4 flex items-center justify-end gap-2 border-t pt-4">
                        <button
                            type="button"
                            onClick={() => setConfirmCancelOpen(true)}
                            className="h-9 rounded border px-3 text-sm"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            className="h-9 rounded bg-primary px-3 text-sm text-primary-foreground disabled:opacity-50"
                            disabled={form.processing}
                        >
                            Save Changes
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
                            onClose();
                        }}
                    />
                </form>
            </div>
        </div>
    );
}

function ProductCard({ product, categories = [] }: ProductCardProps) {
    const [editing, setEditing] = useState(false);
    const [confirmDeleteOpen, setConfirmDeleteOpen] = useState(false);
    const form = useForm({});

    const doDelete = () => {
        form.delete(productsDestroy(product.id).url, {
            preserveScroll: true,
        });
    };

    const hasDiscount = product.discount && product.discount > 0;
    const hasVat = product.has_vat !== false;
    const basePrice = Number(product.price) || 0;
    // VAT-inclusive pricing: the price IS the selling price
    const sellingPrice = hasDiscount
        ? basePrice * (1 - (product.discount ?? 0) / 100)
        : basePrice;

    // Proper boolean check for is_activated
    const isActive =
        product.is_activated === true || product.is_activated === 1;

    return (
        <>
            <div className="group relative flex flex-col overflow-hidden rounded-lg border border-sidebar-border/60 bg-background shadow-sm transition hover:shadow-md dark:border-sidebar-border">
                {!isActive && (
                    <div className="absolute top-2 left-2 z-10 rounded bg-red-500 px-2 py-0.5 text-xs text-white">
                        Inactive
                    </div>
                )}
                {hasDiscount && (
                    <div className="absolute top-2 right-2 z-10 rounded bg-green-500 px-2 py-0.5 text-xs text-white">
                        -{product.discount}%
                    </div>
                )}
                {product.image_url ? (
                    <img
                        src={product.image_url}
                        alt={product.name}
                        className="aspect-4/3 w-full object-cover"
                    />
                ) : (
                    <div className="aspect-4/3 w-full items-center justify-center bg-muted/40 text-muted-foreground">
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
                                {Array.isArray(product.category_name)
                                    ? (product.category_name[0] ??
                                      'Uncategorized')
                                    : (product.category_name ??
                                      'Uncategorized')}
                            </div>
                            {product.sku && (
                                <div className="text-xs text-muted-foreground">
                                    SKU: {product.sku}
                                </div>
                            )}
                        </div>
                        <div className="shrink-0 text-right">
                            {hasDiscount ? (
                                <>
                                    <div className="text-xs text-muted-foreground line-through">
                                        ₱{basePrice.toFixed(2)}
                                    </div>
                                    <div className="rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">
                                        ₱{sellingPrice.toFixed(2)}
                                    </div>
                                </>
                            ) : (
                                <div className="rounded bg-muted px-2 py-0.5 text-xs font-semibold">
                                    ₱{sellingPrice.toFixed(2)}
                                </div>
                            )}
                            <div className="text-xs text-muted-foreground">
                                {hasVat ? 'incl. VAT' : 'VAT exempt'}
                            </div>
                        </div>
                    </div>
                    {product.description && (
                        <div className="line-clamp-2 text-xs text-muted-foreground">
                            {product.description}
                        </div>
                    )}
                    <div className="flex flex-wrap gap-1 text-xs">
                        {product.track_inventory &&
                            !product.has_unlimited_stock && (
                                <span
                                    className={`rounded px-1.5 py-0.5 ${(product.stock ?? 0) <= 5 ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'}`}
                                >
                                    Stock: {product.stock ?? 0}
                                </span>
                            )}
                        {product.has_unlimited_stock && (
                            <span className="rounded bg-purple-100 px-1.5 py-0.5 text-purple-700">
                                Unlimited
                            </span>
                        )}
                    </div>
                    <div className="mt-auto flex items-center justify-end gap-2 pt-1">
                        <button
                            onClick={() => setEditing(true)}
                            className="inline-flex items-center gap-1 rounded border px-2 py-1 text-xs hover:bg-muted"
                        >
                            <Pencil className="h-3 w-3" /> Edit
                        </button>
                        <button
                            onClick={() => setConfirmDeleteOpen(true)}
                            className="inline-flex items-center gap-1 rounded border px-2 py-1 text-xs text-red-600 hover:bg-red-50"
                        >
                            <Trash2 className="h-3 w-3" /> Delete
                        </button>
                    </div>
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
            <EditProductModal
                product={product}
                categories={categories}
                open={editing}
                onClose={() => setEditing(false)}
            />
        </>
    );
}

export default function ProductsIndex() {
    const { props } = usePage<ProductPageProps>();
    const { products, categories, filters } = props;

    const { search, setSearch, category, setCategory } = useQuerySync(
        filters ?? {},
    );
    const [openCreate, setOpenCreate] = useState(false);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Products" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between gap-2">
                    <div>
                        <h1 className="text-xl font-semibold">Products</h1>
                        <p className="text-sm text-muted-foreground">
                            Browse and manage your products.
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

                {products.data.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-12 text-center">
                        <Package className="mb-4 h-12 w-12 text-muted-foreground" />
                        <p className="text-sm text-muted-foreground">
                            No products found.
                        </p>
                    </div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {products.data.map((p) => (
                            <ProductCard
                                key={p.id}
                                product={p}
                                categories={categories}
                            />
                        ))}
                    </div>
                )}
            </div>

            <CreateProductModal
                open={openCreate}
                onClose={() => setOpenCreate(false)}
                categories={categories}
            />
        </AppLayout>
    );
}
