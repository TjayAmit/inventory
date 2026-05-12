import type { Invoice } from './invoice';

export type Transaction = Invoice;

export type TransactionIndexProps = {
    data: {
        data: Transaction[];
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
        date_from?: string;
        date_to?: string;
    };
    statusOptions: string[];
    paymentMethods: string[];
};

export type TransactionShowProps = {
    transaction: Transaction;
};
