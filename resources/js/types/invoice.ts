import type { Branch } from './branch';

export type PaymentMethod = 'cash' | 'gcash' | 'maya';
export type InvoiceStatus = 'draft' | 'pending' | 'paid' | 'completed' | 'cancelled' | 'refunded';
export type InvoicePaymentStatus = 'pending' | 'partial' | 'paid' | 'refunded';

export type InvoiceItem = {
    id: number;
    sales_order_id: number;
    product_id: number;
    quantity: number;
    unit_price: string;
    unit_cost: string;
    discount_amount: string;
    tax_amount: string;
    total_price: string;
    total_cost: string;
    profit: string;
    product?: {
        id: number;
        name: string;
        sku: string;
        unit: string | null;
    };
};

export type Invoice = {
    id: number;
    order_number: string;
    branch_id: number;
    cashier_id: number;
    order_date: string;
    order_time: string;
    status: InvoiceStatus;
    subtotal: string;
    tax_amount: string;
    discount_amount: string;
    total_amount: string;
    paid_amount: string;
    change_amount: string;
    payment_status: InvoicePaymentStatus;
    payment_method: PaymentMethod | null;
    notes: string | null;
    branch?: Branch;
    cashier?: { id: number; name: string; email: string };
    items: InvoiceItem[];
    created_at: string;
    updated_at: string;
};

export type InvoiceProduct = {
    id: number;
    name: string;
    sku: string;
    selling_price: string;
    cost_price: string;
    unit: string | null;
    is_taxable: boolean;
    stock: number;
};

export type InvoiceIndexProps = {
    data: {
        data: Invoice[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: {
        search?: string;
        per_page?: number;
        status?: string;
        payment_method?: string;
    };
    statusOptions: string[];
    paymentMethods: string[];
};

export type InvoiceShowProps = {
    invoice: Invoice;
    products?: InvoiceProduct[];
};
