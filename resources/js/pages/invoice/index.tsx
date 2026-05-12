import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Eye, Plus } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DataTable, DataTablePagination } from '@/components/data-table';
import { index as invoicesIndex, show as invoicesShow, store as invoicesStore } from '@/routes/invoices';
import AppLayout from '@/layouts/app-layout';
import type { InvoiceIndexProps, Invoice, PaymentMethod } from '@/types';

const MODULE_TITLE = 'Invoice';

const STATUS_COLORS: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    paid:      'default',
    completed: 'default',
    cancelled: 'destructive',
    refunded:  'destructive',
    pending:   'outline',
    draft:     'secondary',
};

const PM_LABELS: Record<PaymentMethod, string> = { cash: 'Cash', gcash: 'GCash', maya: 'Maya' };

const fmt = (v: string | number) => `₱${parseFloat(String(v)).toFixed(2)}`;

export default function Index({ data, filters, statusOptions, paymentMethods }: InvoiceIndexProps) {
    const [search, setSearch]             = useState(filters.search ?? '');
    const [perPage, setPerPage]           = useState(filters.per_page ?? 10);
    const [statusFilter, setStatusFilter] = useState(filters.status ?? '');
    const [pmFilter, setPmFilter]         = useState(filters.payment_method ?? '');
    const [creating, setCreating]         = useState(false);

    const navigate = (params: Record<string, unknown> = {}) =>
        router.get(invoicesIndex(), { search, per_page: perPage, status: statusFilter, payment_method: pmFilter, ...params }, { preserveState: true, preserveScroll: true });

    const handleNewInvoice = () => {
        setCreating(true);
        router.post(invoicesStore(), {}, { onFinish: () => setCreating(false) });
    };

    const columns = [
        {
            key: 'order_number',
            header: 'Invoice #',
            cell: (inv: Invoice) => <span className="font-mono text-sm font-medium">{inv.order_number}</span>,
        },
        {
            key: 'date',
            header: 'Date & Time',
            cell: (inv: Invoice) => (
                <div className="flex flex-col">
                    <span className="text-sm">{new Date(inv.order_date).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })}</span>
                    <span className="text-xs text-muted-foreground">{inv.order_time?.slice(0, 5)}</span>
                </div>
            ),
        },
        {
            key: 'branch',
            header: 'Branch',
            cell: (inv: Invoice) => <span className="text-sm">{inv.branch?.name ?? '—'}</span>,
        },
        {
            key: 'cashier',
            header: 'Cashier',
            cell: (inv: Invoice) => <span className="text-sm">{inv.cashier?.name ?? '—'}</span>,
        },
        {
            key: 'total',
            header: 'Total',
            cell: (inv: Invoice) => (
                <div className="flex flex-col gap-0.5">
                    <span className="text-sm font-medium">{fmt(inv.total_amount)}</span>
                    {inv.payment_method && (
                        <span className="text-xs text-muted-foreground">{PM_LABELS[inv.payment_method] ?? inv.payment_method}</span>
                    )}
                </div>
            ),
        },
        {
            key: 'status',
            header: 'Status',
            cell: (inv: Invoice) => (
                <Badge variant={STATUS_COLORS[inv.status] ?? 'secondary'} className="text-xs capitalize">{inv.status}</Badge>
            ),
        },
    ];

    const pagination: DataTablePagination = {
        current_page: data.current_page,
        last_page:    data.last_page,
        per_page:     data.per_page,
        total:        data.total,
        links:        [],
    };

    const actions = [
        {
            key:     'view',
            icon:    <Eye className="w-4 h-4" />,
            label:   'View',
            onClick: (inv: Invoice) => router.get(invoicesShow(inv.id)),
        },
    ];

    const filterContent = (
        <>
            <Select value={statusFilter || 'all'} onValueChange={(v) => { const val = v === 'all' ? '' : v; setStatusFilter(val); navigate({ status: val, page: 1 }); }}>
                <SelectTrigger className="w-36 h-9"><SelectValue placeholder="All Status" /></SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All Status</SelectItem>
                    {statusOptions.map((s) => <SelectItem key={s} value={s} className="capitalize">{s}</SelectItem>)}
                </SelectContent>
            </Select>

            <Select value={pmFilter || 'all'} onValueChange={(v) => { const val = v === 'all' ? '' : v; setPmFilter(val); navigate({ payment_method: val, page: 1 }); }}>
                <SelectTrigger className="w-36 h-9"><SelectValue placeholder="All Payment" /></SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All Payment</SelectItem>
                    {paymentMethods.map((pm) => <SelectItem key={pm} value={pm}>{PM_LABELS[pm as PaymentMethod] ?? pm}</SelectItem>)}
                </SelectContent>
            </Select>

            <Button onClick={handleNewInvoice} disabled={creating} size="sm">
                <Plus className="w-4 h-4 mr-1" />
                {creating ? 'Creating...' : 'New Invoice'}
            </Button>
        </>
    );

    return (
        <>
            <Head title={MODULE_TITLE} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
                <DataTable<Invoice>
                    title={MODULE_TITLE}
                    description="Sales invoices and transaction history"
                    data={data.data}
                    columns={columns}
                    pagination={pagination}
                    searchValue={search}
                    onSearchChange={(v) => { setSearch(v); navigate({ search: v, page: 1 }); }}
                    searchPlaceholder="Search by invoice # or cashier..."
                    filters={filterContent}
                    actions={actions}
                    pageSizeOptions={[10, 25, 50, 100]}
                    onPageSizeChange={(v) => { setPerPage(v); navigate({ per_page: v, page: 1 }); }}
                    onPageChange={(page) => navigate({ page })}
                    emptyTitle="No invoices found"
                    emptyDescription={search ? 'Try a different search or clear filters.' : 'Start by creating a new invoice.'}
                />
            </div>
        </>
    );
}

Index.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={[{ title: MODULE_TITLE, href: invoicesIndex().url }]}>
        {page}
    </AppLayout>
);
