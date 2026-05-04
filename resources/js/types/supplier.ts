export interface Supplier {
    id: number;
    supplier_code: string;
    name: string;
    contact_person: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
    city: string | null;
    payment_terms: number;
    is_active: boolean;
    notes: string | null;
    created_at: string;
    updated_at: string;
    full_address?: string;
    primary_contact?: string;
    primary_email?: string | null;
    formatted_payment_terms?: string;
}

export interface SupplierIndexProps {
    data: {
        data: Supplier[];
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

export interface SupplierFormProps {
    supplier?: Supplier;
}

export interface SupplierEditProps {
    supplier: Supplier;
}

export interface SupplierShowProps {
    supplier: Supplier;
}
