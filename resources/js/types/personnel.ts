import type { Branch } from './branch';

export type Role = {
    id: number;
    name: string;
    guard_name: string;
};

export type Personnel = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    branch_id: number | null;
    branch: Branch | null;
    roles: Role[];
    is_active: boolean;
    created_at: string;
    updated_at: string;
};

export type PersonnelIndexProps = {
    data: {
        data: Personnel[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    branches: Array<{ id: number; name: string }>;
    roles: string[];
    filters: {
        search?: string;
        per_page?: number;
        branch_id?: string;
        role?: string;
    };
};

export type PersonnelFormProps = {
    branches: Array<{ id: number; name: string }>;
    roles: string[];
};

export type PersonnelEditProps = PersonnelFormProps & {
    user: Personnel;
};
