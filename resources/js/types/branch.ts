import type { User } from './auth';

export interface Branch {
    id: number;
    code: string;
    name: string;
    address: string | null;
    city: string | null;
    phone: string | null;
    email: string | null;
    manager_id: number | null;
    manager?: User;
    is_active: boolean;
    is_main_branch: boolean;
    timezone: string | null;
    currency: string | null;
    tax_rate: string | null;
    created_at: string;
    updated_at: string;
}

export interface BranchIndexProps {
    data: {
        data: Branch[];
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

export interface BranchFormProps {
    branch?: Branch;
    managers: Array<{
        id: number;
        name: string;
    }>;
}

export interface BranchEditProps {
    branch: Branch;
    managers: Array<{
        id: number;
        name: string;
    }>;
}

export interface BranchShowProps {
    branch: Branch;
}
