import type { Product } from './products';

export interface MenuCategoryOption {
    id: string;
    name: string;
    slug?: string;
}

export interface MenuPageProps {
    products: Product[];
    categories: MenuCategoryOption[];
    filters: MenuFilters;
    cart?: CartState;
    [key: string]: unknown;
}

export interface MenuFilters {
    search?: string;
    category?: string;
}

export interface CartState {
    count?: number;
    total?: number;
    items?: Array<Record<string, unknown>>;
}

export interface MenuProductCardProps {
    product: Product;
    onAddToCart?: () => void;
}
