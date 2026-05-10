export interface SalesOrderBranch {
    id: number;
    name: string;
}

export interface SalesOrderCashier {
    id: number;
    name: string;
}

export interface SalesOrderItem {
    id: number;
    sales_order_id: number;
    product_id: number;
    product?: {
        id: number;
        name: string;
        sku: string;
    };
    quantity: number;
    unit_price: string;
    total_price: string;
    created_at: string;
    updated_at: string;
}

export interface SalesOrder {
    id: number;
    order_number: string;
    branch_id: number;
    branch?: SalesOrderBranch;
    cashier_id: number;
    cashier?: SalesOrderCashier;
    order_date: string;
    order_time: string | null;
    status: 'pending' | 'confirmed' | 'paid' | 'shipped' | 'completed' | 'cancelled' | 'refunded';
    subtotal: string;
    tax_amount: string;
    discount_amount: string;
    total_amount: string;
    paid_amount: string;
    change_amount: string;
    payment_status: 'pending' | 'partial' | 'paid' | 'refunded';
    notes: string | null;
    created_at: string;
    updated_at: string;
    // Computed attributes
    remaining_amount?: number;
    total_quantity?: number;
    status_label?: string;
    payment_status_label?: string;
}

export interface SalesOrderIndexProps {
    data: {
        data: SalesOrder[];
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
        status?: string;
        payment_status?: string;
    };
    statusOptions: string[];
    paymentStatusOptions: string[];
}

export interface SalesOrderFormProps {
    salesOrder?: SalesOrder;
    branches: SalesOrderBranch[];
    cashiers: SalesOrderCashier[];
}

export interface SalesOrderEditProps {
    salesOrder: SalesOrder;
    branches: SalesOrderBranch[];
    cashiers: SalesOrderCashier[];
}

export interface SalesOrderShowProps {
    salesOrder: SalesOrder & {
        items: SalesOrderItem[];
    };
}
