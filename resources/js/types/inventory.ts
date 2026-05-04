import type { Branch } from './branch';
import type { Product } from './product';

export interface Inventory {
    id: number;
    product_id: number;
    branch_id: number;
    quantity_on_hand: number;
    quantity_reserved: number;
    quantity_available: number;
    average_cost: string;
    last_count_date: string | null;
    last_received_date: string | null;
    is_active: boolean;
    product?: Product;
    branch?: Branch;
    created_at: string;
    updated_at: string;
}

export interface InventoryIndexProps {
    data: {
        data: Inventory[];
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
    products: Array<{
        id: number;
        name: string;
        sku: string;
    }>;
    branches: Array<{
        id: number;
        name: string;
        code: string;
    }>;
}

export interface InventoryFormProps {
    inventory?: Inventory;
    products: Array<{
        id: number;
        name: string;
        sku: string;
    }>;
    branches: Array<{
        id: number;
        name: string;
        code: string;
    }>;
}

export interface InventoryEditProps {
    inventory: Inventory;
    products: Array<{
        id: number;
        name: string;
        sku: string;
    }>;
    branches: Array<{
        id: number;
        name: string;
        code: string;
    }>;
}

export interface InventoryShowProps {
    inventory: Inventory;
}
