import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Eye, Pencil, Trash2, ShoppingCart, Filter } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DataTable, DataTablePagination } from '@/components/data-table';
import { DeleteConfirmDialog } from '@/components/delete-confirm-dialog';
import {
    index as salesOrdersIndex,
    create as salesOrdersCreate,
    show as salesOrdersShow,
    edit as salesOrdersEdit,
    destroy as salesOrdersDestroy,
} from '@/routes/sales-orders';
import type { SalesOrderIndexProps, SalesOrder } from '@/types/sales-order';

const MODULE_TITLE = 'Sales Orders';

const statusColors: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    pending: 'outline',
    confirmed: 'secondary',
    paid: 'default',
    shipped: 'secondary',
    completed: 'default',
    cancelled: 'destructive',
    refunded: 'destructive',
};

const paymentStatusColors: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    pending: 'outline',
    partial: 'secondary',
    paid: 'default',
    refunded: 'destructive',
};

export default function Index({ data, filters, statusOptions, paymentStatusOptions }: SalesOrderIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [perPage, setPerPage] = useState(filters.per_page || 10);
    const [statusFilter, setStatusFilter] = useState(filters.status || '');
    const [paymentStatusFilter, setPaymentStatusFilter] = useState(filters.payment_status || '');
    const [deleteId, setDeleteId] = useState<number | null>(null);
    const [deleteOrderNumber, setDeleteOrderNumber] = useState<string>('');

    const navigate = (params: Record<string, unknown> = {}) => {
        router.get(
            salesOrdersIndex(),
            { search, per_page: perPage, status: statusFilter, payment_status: paymentStatusFilter, ...params },
            { preserveState: true, preserveScroll: true },
        );
    };

    const handleSearchChange = (value: string) => {
        setSearch(value);
        navigate({ search: value, page: 1 });
    };

    const handlePerPageChange = (value: number) => {
        setPerPage(value);
        navigate({ per_page: value, page: 1 });
    };

    const handleStatusChange = (value: string) => {
        setStatusFilter(value === 'all' ? '' : value);
        navigate({ status: value === 'all' ? '' : value, page: 1 });
    };

    const handlePaymentStatusChange = (value: string) => {
        setPaymentStatusFilter(value === 'all' ? '' : value);
        navigate({ payment_status: value === 'all' ? '' : value, page: 1 });
    };

    const handleDelete = () => {
        if (!deleteId) return;
        router.delete(salesOrdersDestroy(deleteId), {
            onFinish: () => {
                setDeleteId(null);
                setDeleteOrderNumber('');
            },
        });
    };

    const columns = [
        {
            key: 'order_number',
            header: 'Order #',
            cell: (order: SalesOrder) => (
                <span className="text-sm font-medium text-foreground font-mono">
                    {order.order_number}
                </span>
            ),
        },
        {
            key: 'date',
            header: 'Date',
            cell: (order: SalesOrder) => (
                <div className="flex flex-col">
                    <span className="text-sm text-foreground">
                        {new Date(order.order_date).toLocaleDateString()}
                    </span>
                    {order.order_time && (
                        <span className="text-xs text-muted-foreground">
                            {order.order_time}
                        </span>
                    )}
                </div>
            ),
        },
        {
            key: 'branch',
            header: 'Branch',
            cell: (order: SalesOrder) => (
                <span className="text-sm text-foreground">
                    {order.branch?.name || '-'}
                </span>
            ),
        },
        {
            key: 'cashier',
            header: 'Cashier',
            cell: (order: SalesOrder) => (
                <span className="text-sm text-foreground">
                    {order.cashier?.name || '-'}
                </span>
            ),
        },
        {
            key: 'total',
            header: 'Total',
            cell: (order: SalesOrder) => (
                <div className="flex flex-col">
                    <span className="text-sm font-medium text-foreground">
                        ${parseFloat(order.total_amount).toFixed(2)}
                    </span>
                    <span className="text-xs text-muted-foreground">
                        Paid: ${parseFloat(order.paid_amount).toFixed(2)}
                    </span>
                </div>
            ),
        },
        {
            key: 'status',
            header: 'Status',
            cell: (order: SalesOrder) => (
                <div className="flex flex-col gap-1">
                    <Badge variant={statusColors[order.status] || 'secondary'} className="text-xs w-fit">
                        {order.status_label || order.status}
                    </Badge>
                    <Badge variant={paymentStatusColors[order.payment_status] || 'secondary'} className="text-xs w-fit">
                        {order.payment_status_label || order.payment_status}
                    </Badge>
                </div>
            ),
        },
    ];

    const pagination: DataTablePagination = {
        current_page: data.current_page,
        last_page: data.last_page,
        per_page: data.per_page,
        total: data.total,
        links: [],
    };

    const actions = [
        {
            key: 'view',
            icon: <Eye className="w-4 h-4" />,
            label: 'View',
            onClick: (order: SalesOrder) => router.get(salesOrdersShow(order.id))
        },
        {
            key: 'edit',
            icon: <Pencil className="w-4 h-4" />,
            label: 'Edit',
            onClick: (order: SalesOrder) => router.get(salesOrdersEdit(order.id))
        },
        {
            key: 'delete',
            icon: <Trash2 className="w-4 h-4" />,
            label: 'Delete',
            onClick: (order: SalesOrder) => {
                setDeleteId(order.id);
                setDeleteOrderNumber(order.order_number);
            },
            variant: 'destructive' as const
        },
    ];

    const filterContent = (
        <>
            <Select value={statusFilter || 'all'} onValueChange={handleStatusChange}>
                <SelectTrigger className="w-[140px] h-10 bg-muted/50">
                    <Filter className="w-4 h-4 mr-2" />
                    <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All Status</SelectItem>
                    {statusOptions.map((status) => (
                        <SelectItem key={status} value={status}>
                            {status.charAt(0).toUpperCase() + status.slice(1)}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>

            <Select value={paymentStatusFilter || 'all'} onValueChange={handlePaymentStatusChange}>
                <SelectTrigger className="w-[160px] h-10 bg-muted/50">
                    <Filter className="w-4 h-4 mr-2" />
                    <SelectValue placeholder="Payment" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All Payment</SelectItem>
                    {paymentStatusOptions.map((status) => (
                        <SelectItem key={status} value={status}>
                            {status.charAt(0).toUpperCase() + status.slice(1)}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </>
    );

    return (
        <>
            <Head title={MODULE_TITLE} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
                <DataTable<SalesOrder>
                    title={MODULE_TITLE}
                    description="Manage sales orders and transactions"
                    data={data.data}
                    columns={columns}
                    pagination={pagination}
                    searchValue={search}
                    onSearchChange={handleSearchChange}
                    searchPlaceholder={`Search ${MODULE_TITLE.toLowerCase()}...`}
                    filters={filterContent}
                    actions={actions}
                    createHref={salesOrdersCreate().url}
                    createLabel="New Sales Order"
                    pageSizeOptions={[10, 25, 50, 100]}
                    onPageSizeChange={handlePerPageChange}
                    onPageChange={(page) => navigate({ page })}
                    emptyTitle={`No ${MODULE_TITLE.toLowerCase()} found`}
                    emptyDescription={search ? 'Try a different search or clear the filters.' : undefined}
                />
            </div>

            <DeleteConfirmDialog
                isOpen={!!deleteId}
                onClose={() => {
                    setDeleteId(null);
                    setDeleteOrderNumber('');
                }}
                onConfirm={handleDelete}
                title="Delete Sales Order"
                itemName={deleteOrderNumber}
                description="This will permanently delete this sales order and all associated items. This action cannot be undone."
            />
        </>
    );
}
