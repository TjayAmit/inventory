import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Eye, TrendingDown, Package, AlertTriangle, CheckCircle, XCircle } from 'lucide-react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { DataTable, DataTableColumn, DataTableAction, useDebouncedValue } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import AppContentWrapper from '@/components/app-content-wrapper';

interface StockItem {
    id: number;
    productId: number;
    productName: string;
    productCode: string;
    productBarcode: string | null;
    categoryName: string | null;
    unit: string;
    quantity: number;
    reorderPoint: number;
    maxStock: number;
    isLowStock: boolean;
    isInStock: boolean;
    isOverStock: boolean;
    stockStatus: 'in_stock' | 'low_stock' | 'out_of_stock' | 'over_stock';
    lastRestockedAt: string | null;
    hasStockRecord: boolean;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Stats {
    total_products: number;
    tracked_products: number;
    untracked: number;
    in_stock: number;
    low_stock: number;
    out_of_stock: number;
    total_units: number;
}

interface CategoryOption {
    id: number;
    name: string;
}

interface StocksIndexProps {
    stocks: {
        data: StockItem[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginationLink[];
    };
    filters: {
        search?: string;
        category_id?: string;
        stock_status?: string;
    };
    stats: Stats;
    categories: CategoryOption[];
    can: {
        adjust: boolean;
    };
}

const stockStatusConfig = {
    in_stock: { label: 'In Stock', variant: 'default' as const, icon: CheckCircle, className: 'bg-green-100 text-green-800 border-green-200' },
    low_stock: { label: 'Low Stock', variant: 'secondary' as const, icon: AlertTriangle, className: 'bg-yellow-100 text-yellow-800 border-yellow-200' },
    out_of_stock: { label: 'Out of Stock', variant: 'destructive' as const, icon: XCircle, className: 'bg-red-100 text-red-800 border-red-200' },
    over_stock: { label: 'Overstocked', variant: 'outline' as const, icon: TrendingDown, className: 'bg-blue-100 text-blue-800 border-blue-200' },
};

export default function StocksIndex({ stocks, filters, stats, categories, can }: StocksIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [categoryFilter, setCategoryFilter] = useState(filters.category_id || '');
    const [stockStatusFilter, setStockStatusFilter] = useState(filters.stock_status || '');
    const debouncedSearch = useDebouncedValue(search, 500);
    const isInitialMount = React.useRef(true);

    React.useEffect(() => {
        if (isInitialMount.current) {
            isInitialMount.current = false;
            return;
        }

        router.get('/stocks', {
            search: debouncedSearch || undefined,
            category_id: categoryFilter || undefined,
            stock_status: stockStatusFilter || undefined,
        }, { preserveState: true, replace: true });
    }, [debouncedSearch, categoryFilter, stockStatusFilter]);

    const handlePageChange = (page: number) => {
        router.get('/stocks', {
            page,
            search: debouncedSearch || undefined,
            category_id: categoryFilter || undefined,
            stock_status: stockStatusFilter || undefined,
        }, { preserveState: true });
    };

    const handlePageSizeChange = (size: number) => {
        router.get('/stocks', {
            page: 1,
            per_page: size,
            search: debouncedSearch || undefined,
            category_id: categoryFilter || undefined,
            stock_status: stockStatusFilter || undefined,
        }, { preserveState: true });
    };

    const columns: DataTableColumn<StockItem>[] = [
        {
            key: 'productName',
            header: 'Product',
            cell: (item) => (
                <div className="flex flex-col">
                    <span className="font-medium text-foreground">{item.productName}</span>
                    <span className="text-xs text-muted-foreground">{item.productCode}</span>
                </div>
            ),
        },
        {
            key: 'categoryName',
            header: 'Category',
            cell: (item) => (
                <span className="text-sm text-muted-foreground">{item.categoryName ?? '—'}</span>
            ),
        },
        {
            key: 'quantity',
            header: 'Current Stock',
            cell: (item) => (
                <div className="flex flex-col gap-1">
                    <span className="font-semibold text-foreground text-base">
                        {item.quantity} <span className="text-xs font-normal text-muted-foreground">{item.unit}</span>
                    </span>
                    {!item.hasStockRecord && (
                        <span className="text-xs text-muted-foreground italic">Not tracked</span>
                    )}
                </div>
            ),
        },
        {
            key: 'reorderPoint',
            header: 'Reorder / Max',
            cell: (item) => (
                <span className="text-sm text-muted-foreground">
                    {item.reorderPoint} / {item.maxStock}
                </span>
            ),
        },
        {
            key: 'stockStatus',
            header: 'Status',
            cell: (item) => {
                const config = stockStatusConfig[item.stockStatus];
                return (
                    <span className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium border ${config.className}`}>
                        <config.icon className="w-3 h-3" />
                        {config.label}
                    </span>
                );
            },
        },
        {
            key: 'lastRestockedAt',
            header: 'Last Restocked',
            cell: (item) => (
                <span className="text-sm text-muted-foreground">
                    {item.lastRestockedAt
                        ? new Date(item.lastRestockedAt).toLocaleDateString()
                        : '—'}
                </span>
            ),
        },
    ];

    const actions: DataTableAction<StockItem>[] = [
        {
            key: 'view',
            icon: <Eye className="w-4 h-4" />,
            label: can.adjust ? 'Manage Stock' : 'View Stock',
            href: (item) => `/stocks/${item.productId}`,
            variant: 'ghost',
        },
    ];

    const filtersComponent = (
        <div className="flex gap-2">
            <Select
                value={stockStatusFilter || undefined}
                onValueChange={(v) => setStockStatusFilter(v === 'all' ? '' : v)}
            >
                <SelectTrigger className="w-40 h-10 bg-muted/50 border-border rounded-lg">
                    <SelectValue placeholder="All Statuses" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All Statuses</SelectItem>
                    <SelectItem value="in_stock">In Stock</SelectItem>
                    <SelectItem value="low_stock">Low Stock</SelectItem>
                    <SelectItem value="out_of_stock">Out of Stock</SelectItem>
                </SelectContent>
            </Select>

            <Select
                value={categoryFilter || undefined}
                onValueChange={(v) => setCategoryFilter(v === 'all' ? '' : v)}
            >
                <SelectTrigger className="w-40 h-10 bg-muted/50 border-border rounded-lg">
                    <SelectValue placeholder="All Categories" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All Categories</SelectItem>
                    {categories.map((cat) => (
                        <SelectItem key={cat.id} value={String(cat.id)}>{cat.name}</SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );

    return (
        <>
            <Head title="Stock Management" />
            <AppContentWrapper>
                {/* Stats Cards */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center gap-3">
                                <div className="p-2 rounded-lg bg-green-100">
                                    <CheckCircle className="w-5 h-5 text-green-600" />
                                </div>
                                <div>
                                    <p className="text-2xl font-bold text-foreground">{stats.in_stock}</p>
                                    <p className="text-xs text-muted-foreground">In Stock</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center gap-3">
                                <div className="p-2 rounded-lg bg-yellow-100">
                                    <AlertTriangle className="w-5 h-5 text-yellow-600" />
                                </div>
                                <div>
                                    <p className="text-2xl font-bold text-foreground">{stats.low_stock}</p>
                                    <p className="text-xs text-muted-foreground">Low Stock</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center gap-3">
                                <div className="p-2 rounded-lg bg-red-100">
                                    <XCircle className="w-5 h-5 text-red-600" />
                                </div>
                                <div>
                                    <p className="text-2xl font-bold text-foreground">{stats.out_of_stock}</p>
                                    <p className="text-xs text-muted-foreground">Out of Stock</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center gap-3">
                                <div className="p-2 rounded-lg bg-blue-100">
                                    <Package className="w-5 h-5 text-blue-600" />
                                </div>
                                <div>
                                    <p className="text-2xl font-bold text-foreground">{stats.total_units.toLocaleString()}</p>
                                    <p className="text-xs text-muted-foreground">Total Units</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <DataTable
                    key={`${stocks.current_page}-${stockStatusFilter}-${categoryFilter}`}
                    title="Stock Management"
                    description="Track and manage product inventory levels"
                    data={stocks.data}
                    columns={columns}
                    pagination={{
                        current_page: stocks.current_page,
                        last_page: stocks.last_page,
                        per_page: stocks.per_page,
                        total: stocks.total,
                        links: stocks.links,
                    }}
                    searchValue={search}
                    onSearchChange={setSearch}
                    searchPlaceholder="Search by product name or code..."
                    filters={filtersComponent}
                    actions={actions}
                    emptyTitle="No products found"
                    emptyDescription="No products match your current filters."
                    onPageChange={handlePageChange}
                    onPageSizeChange={handlePageSizeChange}
                />
            </AppContentWrapper>
        </>
    );
}

StocksIndex.layout = {
    breadcrumbs: [
        { title: 'Stock Management', href: '/stocks' },
    ],
};
