import type { User } from './auth';

export interface UserIndexProps {
    data: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        search?: string;
        per_page?: number;
    };
}

export interface UserFormProps {
    user?: User;
}

export interface UserShowProps {
    user: User;
}
