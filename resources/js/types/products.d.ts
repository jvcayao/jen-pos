import type { CategoryOption } from './category';

export interface Product {
    id: number;
    name: string;
    description?: string | null;
    price: string | number;
    image_url?: string | null;
    category_id?: string | null;
    category_parent_id?: string | null;
    category_name?: string | null;
}

export interface ProductPageProps {
    products: Product[];
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
    price: number | string;
    category_id: string | '';
    image: File | null;
}
