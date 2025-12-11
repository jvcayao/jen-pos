export interface CategoryNode {
    id: number;
    name: string;
    slug: string | null;
    children?: CategoryNode[];
}

export interface CategoryOption {
    id: string;
    name: string;
    slug: string;
}

export interface CategoryPageProps {
    categories: CategoryNode[];
    [key: string]: unknown;
}
