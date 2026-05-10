export interface SalesItem {
    id: number;
    sales_order_id: number;
    product_id: number;
    inventory_batch_id: number | null;
    quantity: number;
    unit_price: string;
    unit_cost: string | null;
    discount_amount: string | null;
    tax_amount: string | null;
    total_price: string;
    total_cost: string | null;
    profit: string | null;
    salesOrder?: {
        id: number;
        order_number: string;
    };
    product?: {
        id: number;
        name: string;
        sku: string;
    };
    inventoryBatch?: {
        id: number;
        batch_number: string;
    };
    created_at: string;
    updated_at: string;
}

export interface SalesItemIndexProps {
    data: {
        data: SalesItem[];
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
    salesOrders: Array<{
        id: number;
        order_number: string;
    }>;
    products: Array<{
        id: number;
        name: string;
        sku: string;
    }>;
}

export interface SalesItemFormProps {
    salesItem?: SalesItem;
    salesOrders: Array<{
        id: number;
        order_number: string;
    }>;
    products: Array<{
        id: number;
        name: string;
        sku: string;
    }>;
    inventoryBatches: Array<{
        id: number;
        batch_number: string;
    }>;
}

export interface SalesItemEditProps {
    salesItem: SalesItem;
    salesOrders: Array<{
        id: number;
        order_number: string;
    }>;
    products: Array<{
        id: number;
        name: string;
        sku: string;
    }>;
    inventoryBatches: Array<{
        id: number;
        batch_number: string;
    }>;
}

export interface SalesItemShowProps {
    salesItem: SalesItem;
}
