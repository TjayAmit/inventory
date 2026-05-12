import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Eye, ArrowUpRight, Banknote, CreditCard, Smartphone } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DataTable, DataTablePagination } from '@/components/data-table';
import { index as txIndex, show as txShow } from '@/routes/transactions';
import AppLayout from '@/layouts/app-layout';
import type { TransactionIndexProps, Transaction, PaymentMethod } from '@/types';

const MODULE_TITLE = 'Transactions';

const STATUS_COLORS: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    paid:      'default',
    completed: 'default',
    cancelled: 'destructive',
    refunded:  'destructive',
};

const STATUS_LABELS: Record<string, string> = {
    paid:      'Paid',
    completed: 'Completed',
    cancelled: 'Cancelled',
    refunded:  'Refunded',
};

const PM_LABELS: Record<PaymentMethod, string> = { cash: 'Cash', gcash: 'GCash', maya: 'Maya' };

const PM_ICONS: Record<PaymentMethod, React.ReactNode> = {
    cash:  <Banknote className="h-3 w-3" />,
    gcash: <Smartphone className="h-3 w-3" />,
    maya:  <CreditCard className="h-3 w-3" />,
};

const fmt = (v: string | number) => `₱${parseFloat(String(v)).toFixed(2)}`;

export default function Index({ data, filters, statusOptions, paymentMethods }: TransactionIndexProps) {
    const [search, setSearch]             = useState(filters.search ?? '');
    const [perPage, setPerPage]           = useState(filters.per_page ?? 15);
    const [statusFilter, setStatusFilter] = useState(filters.status ?? '');
    const [pmFilter, setPmFilter]         = useState(filters.payment_method ?? '');
    const [dateFrom, setDateFrom]         = useState(filters.date_from ?? '');
    const [dateTo, setDateTo]             = useState(filters.date_to ?? '');

    const navigate = (params: Record<string, unknown> = {}) =>
        router.get(
            txIndex(),
            { search, per_page: perPage, status: statusFilter, payment_method: pmFilter, date_from: dateFrom, date_to: dateTo, ...params },
            { preserveState: true, preserveScroll: true },
        );

    const columns = [
        {
            key: 'order_number',
            header: 'Reference',
            cell: (tx: Transaction) => (
                <span className="font-mono text-sm font-semibold tracking-tight">{tx.order_number}</span>
            ),
        },
        {
            key: 'date',
            header: 'Date & Time',
            cell: (tx: Transaction) => (
                <div className="flex flex-col">
                    <span className="text-sm">
                        {new Date(tx.order_date).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })}
                    </span>
                    <span className="text-xs text-muted-foreground tabular-nums">{tx.order_time?.slice(0, 5)}</span>
                </div>
            ),
        },
        {
            key: 'branch',
            header: 'Branch',
            cell: (tx: Transaction) => (
                <span className="text-sm">{tx.branch?.name ?? '—'}</span>
            ),
        },
        {
            key: 'cashier',
            header: 'Cashier',
            cell: (tx: Transaction) => (
                <span className="text-sm">{tx.cashier?.name ?? '—'}</span>
            ),
        },
        {
            key: 'items',
            header: 'Items',
            cell: (tx: Transaction) => (
                <span className="text-sm tabular-nums text-muted-foreground">
                    {tx.items?.length ?? 0} item{(tx.items?.length ?? 0) !== 1 ? 's' : ''}
                </span>
            ),
        },
        {
            key: 'total',
            header: 'Total',
            cell: (tx: Transaction) => (
                <div className="flex flex-col gap-0.5">
                    <span className="text-sm font-semibold tabular-nums">{fmt(tx.total_amount)}</span>
                    {tx.payment_method && (
                        <span className="flex items-center gap-1 text-xs text-muted-foreground">
                            {PM_ICONS[tx.payment_method]}
                            {PM_LABELS[tx.payment_method]}
                        </span>
                    )}
                </div>
            ),
        },
        {
            key: 'status',
            header: 'Status',
            cell: (tx: Transaction) => (
                <Badge variant={STATUS_COLORS[tx.status] ?? 'secondary'} className="text-xs">
                    {STATUS_LABELS[tx.status] ?? tx.status}
                </Badge>
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
            onClick: (tx: Transaction) => router.get(txShow(tx.id)),
        },
    ];

    const filterContent = (
        <>
            <Select
                value={statusFilter || 'all'}
                onValueChange={(v) => { const val = v === 'all' ? '' : v; setStatusFilter(val); navigate({ status: val, page: 1 }); }}
            >
                <SelectTrigger className="w-36 h-9"><SelectValue placeholder="All Status" /></SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All Status</SelectItem>
                    {statusOptions.map((s) => (
                        <SelectItem key={s} value={s}>{STATUS_LABELS[s] ?? s}</SelectItem>
                    ))}
                </SelectContent>
            </Select>

            <Select
                value={pmFilter || 'all'}
                onValueChange={(v) => { const val = v === 'all' ? '' : v; setPmFilter(val); navigate({ payment_method: val, page: 1 }); }}
            >
                <SelectTrigger className="w-32 h-9"><SelectValue placeholder="All Payment" /></SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All Payment</SelectItem>
                    {paymentMethods.map((pm) => (
                        <SelectItem key={pm} value={pm}>{PM_LABELS[pm as PaymentMethod] ?? pm}</SelectItem>
                    ))}
                </SelectContent>
            </Select>

            <div className="flex items-center gap-1.5">
                <Input
                    type="date"
                    value={dateFrom}
                    onChange={(e) => { setDateFrom(e.target.value); navigate({ date_from: e.target.value, page: 1 }); }}
                    className="h-9 w-36 text-xs"
                    placeholder="From"
                />
                <span className="text-muted-foreground text-xs">–</span>
                <Input
                    type="date"
                    value={dateTo}
                    onChange={(e) => { setDateTo(e.target.value); navigate({ date_to: e.target.value, page: 1 }); }}
                    className="h-9 w-36 text-xs"
                    placeholder="To"
                />
            </div>
        </>
    );

    return (
        <>
            <Head title={MODULE_TITLE} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
                <DataTable<Transaction>
                    title={MODULE_TITLE}
                    description="Completed sales transaction history"
                    data={data.data}
                    columns={columns}
                    pagination={pagination}
                    searchValue={search}
                    onSearchChange={(v) => { setSearch(v); navigate({ search: v, page: 1 }); }}
                    searchPlaceholder="Search by reference, cashier, or branch…"
                    filters={filterContent}
                    actions={actions}
                    pageSizeOptions={[15, 25, 50, 100]}
                    onPageSizeChange={(v) => { setPerPage(v); navigate({ per_page: v, page: 1 }); }}
                    onPageChange={(page) => navigate({ page })}
                    emptyTitle="No transactions found"
                    emptyDescription={search ? 'Try a different search or clear filters.' : 'Completed invoices will appear here.'}
                />
            </div>
        </>
    );
}

Index.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={[{ title: MODULE_TITLE, href: txIndex().url }]}>
        {page}
    </AppLayout>
);
