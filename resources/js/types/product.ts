export interface ProductCategory {
    id: number;
    name: string;
}

export interface Product {
    id: number;
    sku: string;
    barcode: string | null;
    name: string;
    slug: string;
    description: string | null;
    category_id: number | null;
    category?: ProductCategory;
    brand: string | null;
    unit: string;
    cost_price: string;
    selling_price: string;
    min_price: string | null;
    reorder_level: number;
    reorder_quantity: number;
    is_active: boolean;
    is_taxable: boolean;
    is_trackable: boolean;
    image_urls: string[] | null;
    created_at: string;
    updated_at: string;
}

export interface ProductIndexProps {
    data: {
        data: Product[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    filters: {
        search?: string;
        per_page?: number;
    };
}

export interface ProductFormProps {
    product?: Product;
    categories: ProductCategory[];
}

export interface ProductEditProps {
    product: Product;
    categories: ProductCategory[];
}

export interface ProductShowProps {
    product: Product;
}
