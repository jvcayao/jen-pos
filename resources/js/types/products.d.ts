import type { CategoryOption } from './category';

export interface Product {
    id: number;
    name: string;
    description?: string | null;
    slug?: string;
    sku?: string | null;
    barcode?: string | null;
    price: string | number;
    discount?: number;
    discount_to?: string | null;
    vat?: number;
    has_vat?: boolean;
    stock?: number;
    track_inventory?: boolean;
    is_activated?: boolean;
    has_unlimited_stock?: boolean;
    has_max_cart?: boolean;
    min_cart?: number | null;
    max_cart?: number | null;
    has_stock_alert?: boolean;
    min_stock_alert?: number | null;
    max_stock_alert?: number | null;
    image_url?: string | null;
    category_id?: string | null;
    category_parent_id?: string | null;
    category_name?: string | null;
}

export interface PaginatedProducts {
    data: Product[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export interface ProductPageProps {
    products: PaginatedProducts;
    categories: CategoryOption[];
    filters: ProductFilters;
    [key: string]: unknown;
}

export interface ProductFilters {
    search?: string;
    category?: string;
}

export interface ProductCardProps {
    product: Product;
    categories?: CategoryOption[];
    onAddToCart?: () => void;
}

export interface CreateProductModalProps {
    open: boolean;
    onClose: () => void;
    categories: CategoryOption[];
}

export interface ProductFormData {
    name: string;
    description: string;
    sku: string;
    barcode: string;
    price: number | string;
    discount: number | string;
    discount_to: string;
    vat: number | string;
    has_vat: boolean;
    stock: number | string;
    track_inventory: boolean;
    is_activated: boolean;
    has_unlimited_stock: boolean;
    has_max_cart: boolean;
    min_cart: number | string;
    max_cart: number | string;
    has_stock_alert: boolean;
    min_stock_alert: number | string;
    max_stock_alert: number | string;
    category_id: string | '';
    image: File | null;
}
