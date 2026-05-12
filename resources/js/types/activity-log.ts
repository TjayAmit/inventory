export type ActivityLog = {
    id: number;
    log_name: string | null;
    description: string;
    subject_type: string | null;
    subject_id: number | null;
    event: string | null;
    causer_type: string | null;
    causer_id: number | null;
    properties: {
        attributes?: Record<string, unknown>;
        old?: Record<string, unknown>;
    } | null;
    batch_uuid: string | null;
    causer: { id: number; name: string; email: string } | null;
    created_at: string;
    updated_at: string;
};

export type ActivityLogIndexProps = {
    data: {
        data: ActivityLog[];
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
        log_name?: string;
        event?: string;
        date_from?: string;
        date_to?: string;
    };
    logNames: string[];
    events: string[];
};
