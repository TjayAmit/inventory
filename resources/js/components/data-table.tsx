import React from 'react';
import { Link } from '@inertiajs/react';
import { Search, Eye, Edit, Trash2, ChevronLeft, ChevronRight, MoreHorizontal, ChevronFirst, ChevronLast } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';

// Column definition types
export type DataTableColumn<T> = {
    key: string;
    header: string;
    cell: (item: T) => React.ReactNode;
    width?: string;
    align?: 'left' | 'center' | 'right';
    sortable?: boolean;
};

// Filter configuration
export type DataTableFilter = {
    key: string;
    placeholder: string;
    options: Array<{ value: string; label: string }>;
    value: string;
    onChange: (value: string) => void;
};

// Action configuration
export type DataTableAction<T> = {
    key: string;
    icon: React.ReactNode;
    label: string;
    href?: (item: T) => string;
    onClick?: (item: T) => void;
    variant?: 'default' | 'destructive' | 'ghost';
    visible?: (item: T) => boolean;
    disabled?: (item: T) => boolean;
    tooltip?: (item: T) => string | null;
};

// Pagination link type
export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

// Pagination info
export type DataTablePagination = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
};

// Main DataTable props
export type DataTableProps<T extends { id: number | string }> = {
    title: string;
    description?: string;
    data: T[];
    columns: DataTableColumn<T>[];
    pagination: DataTablePagination;
    searchValue: string;
    onSearchChange: (value: string) => void;
    searchPlaceholder?: string;
    filters?: React.ReactNode;
    actions?: DataTableAction<T>[];
    createHref?: string;
    createLabel?: string;
    emptyTitle?: string;
    emptyDescription?: string;
    onDelete?: (item: T) => void;
    deleteConfirmation?: {
        title: string;
        description: (item: T) => string;
    };
    pageSizeOptions?: number[];
    onPageSizeChange?: (size: number) => void;
    onPageChange?: (page: number) => void;
};

// Reusable DataTable component
export function DataTable<T extends { id: number | string }>({
    title,
    description,
    data,
    columns,
    pagination,
    searchValue,
    onSearchChange,
    searchPlaceholder = 'Search...',
    filters,
    actions,
    createHref,
    createLabel = 'Add New',
    emptyTitle = 'No items found',
    emptyDescription = 'Get started by creating a new item.',
    pageSizeOptions = [10, 25, 50, 100],
    onPageSizeChange,
    onPageChange,
}: DataTableProps<T>) {
    const renderActionButton = (action: DataTableAction<T>, item: T) => {
        const isVisible = action.visible ? action.visible(item) : true;
        const isDisabled = action.disabled ? action.disabled(item) : false;
        const tooltipText = action.tooltip ? action.tooltip(item) : null;

        if (!isVisible) return null;

        const buttonContent = (
            <Button
                variant={action.variant || 'ghost'}
                size="sm"
                onClick={() => action.onClick?.(item)}
                disabled={isDisabled}
                className={cn(
                    'h-8 w-8 p-0 transition-colors duration-150',
                    action.variant === 'destructive' && 'text-destructive hover:text-destructive hover:bg-destructive/10',
                    action.variant === 'ghost' && 'hover:bg-muted/60',
                    isDisabled && 'opacity-50 cursor-not-allowed'
                )}
            >
                {action.icon}
            </Button>
        );

        if (isDisabled && tooltipText) {
            return (
                <TooltipProvider key={action.key}>
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <span>{buttonContent}</span>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>{tooltipText}</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            );
        }

        if (action.href) {
            return (
                <Link key={action.key} href={action.href(item)}>
                    {buttonContent}
                </Link>
            );
        }

        return <React.Fragment key={action.key}>{buttonContent}</React.Fragment>;
    };

    const hasData = data.length > 0;

    return (
        <div className="space-y-4">
            {/* Page Header */}
            <div className="flex justify-between items-center">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight text-foreground">{title}</h1>
                    {description && (
                        <p className="text-muted-foreground mt-1">{description}</p>
                    )}
                </div>
                {createHref && (
                    <Link href={createHref}>
                        <Button className="shadow-sm hover:shadow-md transition-shadow">
                            {createLabel}
                        </Button>
                    </Link>
                )}
            </div>

            {/* Card Content */}
            <div className="bg-card text-card-foreground overflow-hidden shadow-sm rounded-lg border border-border/60">
                {/* Filters Header - Description Left, Actions Right */}
                <div className="border-b border-border bg-muted/30">
                    <div className="p-4">
                        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            {/* Left: Description */}
                            <div className="text-sm text-muted-foreground">
                                {description || `Manage ${title.toLowerCase()} records`}
                            </div>

                            {/* Right: Search and Filters */}
                            <div className="flex flex-col sm:flex-row gap-3 items-start sm:items-center w-full sm:w-auto">
                                {/* Search */}
                                <div className="relative group w-full sm:w-72">
                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <Search className="h-4 w-4 text-muted-foreground group-focus-within:text-primary transition-colors duration-200" />
                                    </div>
                                    <Input
                                        type="text"
                                        value={searchValue}
                                        onChange={(e) => onSearchChange(e.target.value)}
                                        placeholder={searchPlaceholder}
                                        className="pl-10 pr-4 h-10 bg-muted/50 border-border hover:bg-muted focus:bg-background focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg transition-all duration-200"
                                    />
                                </div>

                                {/* Additional Filters */}
                                {filters && (
                                    <div className="flex gap-3 items-center">
                                        {filters}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Table Container */}
                <div className="overflow-x-auto">
                    {hasData ? (
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/50 hover:bg-muted/50 border-b border-border/60">
                                    {columns.map((column) => (
                                        <TableHead
                                            key={column.key}
                                            className={cn(
                                                'font-semibold text-foreground py-3',
                                                column.align === 'center' && 'text-center',
                                                column.align === 'right' && 'text-right',
                                                column.width
                                            )}
                                        >
                                            {column.header}
                                        </TableHead>
                                    ))}
                                    {actions && actions.length > 0 && (
                                        <TableHead className="w-[120px] text-right font-semibold text-foreground py-3">
                                            Actions
                                        </TableHead>
                                    )}
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {data.map((item, index) => (
                                    <TableRow
                                        key={item.id}
                                        className="hover:bg-muted/30 transition-colors duration-150 border-b border-border/40"
                                    >
                                        {columns.map((column) => (
                                            <TableCell
                                                key={`${item.id}-${column.key}`}
                                                className={cn(
                                                    'py-3',
                                                    column.align === 'center' && 'text-center',
                                                    column.align === 'right' && 'text-right'
                                                )}
                                            >
                                                {column.cell(item)}
                                            </TableCell>
                                        ))}
                                        {actions && actions.length > 0 && (
                                            <TableCell className="py-3">
                                                <div className="flex gap-1 justify-end">
                                                    {actions.map((action) => renderActionButton(action, item))}
                                                </div>
                                            </TableCell>
                                        )}
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        /* Empty State */
                        <div className="flex flex-col items-center justify-center py-16 px-4">
                            <div className="w-16 h-16 rounded-full bg-muted/50 flex items-center justify-center mb-4">
                                <Search className="w-8 h-8 text-muted-foreground" />
                            </div>
                            <h3 className="text-lg font-semibold text-foreground mb-1">
                                {emptyTitle}
                            </h3>
                            <p className="text-sm text-muted-foreground text-center max-w-sm">
                                {emptyDescription}
                            </p>
                            {createHref && (
                                <Link href={createHref} className="mt-4">
                                    <Button variant="outline" size="sm">
                                        {createLabel}
                                    </Button>
                                </Link>
                            )}
                        </div>
                    )}
                </div>

                {/* Pagination - Always show if there's data */}
                {hasData && (
                    <div className="border-t border-border bg-muted/20 p-4">
                        <div className="flex flex-col sm:flex-row justify-between items-center gap-4">
                            {/* Left: Page info */}
                            <div className="flex items-center gap-4 order-2 sm:order-1">
                                <span className="text-sm text-muted-foreground">
                                    Page <span className="font-semibold text-foreground">{pagination.current_page}</span> of{' '}
                                    <span className="font-semibold text-foreground">{pagination.last_page}</span>
                                    <span className="ml-2 text-muted-foreground/70">
                                        ({pagination.total} total)
                                    </span>
                                </span>
                                {onPageSizeChange && (
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                className="h-8 px-2 text-xs"
                                            >
                                                {pagination.per_page} / page
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="start">
                                            {pageSizeOptions.map((size) => (
                                                <DropdownMenuItem
                                                    key={size}
                                                    onClick={() => onPageSizeChange(size)}
                                                    className={cn(
                                                        'text-xs cursor-pointer',
                                                        pagination.per_page === size && 'bg-muted font-semibold'
                                                    )}
                                                >
                                                    {size} items per page
                                                </DropdownMenuItem>
                                            ))}
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                )}
                            </div>

                            {/* Right: Navigation buttons - always show but disable if single page */}
                            <div className="flex items-center gap-1 order-1 sm:order-2">
                                {/* First */}
                                {onPageChange ? (
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => onPageChange(1)}
                                        disabled={pagination.current_page <= 1}
                                        className={cn(
                                            'w-9 h-9 p-0',
                                            pagination.current_page <= 1 && 'opacity-40 cursor-not-allowed'
                                        )}
                                    >
                                        <ChevronFirst className="w-5 h-5" />
                                    </Button>
                                ) : (
                                    <Link
                                        href={pagination.links[1]?.url || '#'}
                                        className={cn(
                                            'inline-flex items-center justify-center w-9 h-9 rounded-md text-sm font-medium transition-all duration-200',
                                            pagination.current_page > 1
                                                ? 'text-foreground hover:text-foreground hover:bg-muted'
                                                : 'text-muted-foreground pointer-events-none opacity-40'
                                        )}
                                    >
                                        <ChevronFirst className="w-5 h-5" />
                                    </Link>
                                )}

                                {/* Previous */}
                                {onPageChange ? (
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => onPageChange(Math.max(1, pagination.current_page - 1))}
                                        disabled={!pagination.links[0]?.url}
                                        className={cn(
                                            'w-9 h-9 p-0',
                                            !pagination.links[0]?.url && 'opacity-40 cursor-not-allowed'
                                        )}
                                    >
                                        <ChevronLeft className="w-5 h-5" />
                                    </Button>
                                ) : (
                                    <Link
                                        href={pagination.links[0]?.url || '#'}
                                        className={cn(
                                            'inline-flex items-center justify-center w-9 h-9 rounded-md text-sm font-medium transition-all duration-200',
                                            pagination.links[0]?.url
                                                ? 'text-foreground hover:text-foreground hover:bg-muted'
                                                : 'text-muted-foreground pointer-events-none opacity-40'
                                        )}
                                    >
                                        <ChevronLeft className="w-5 h-5" />
                                    </Link>
                                )}

                                {/* Page Numbers - show just '1' for single page */}
                                <div className="flex items-center gap-1">
                                    {pagination.last_page === 1 ? (
                                        <span className="inline-flex items-center justify-center min-w-[36px] h-9 px-3 rounded-md text-sm font-medium text-primary">
                                            1
                                        </span>
                                    ) : (
                                        pagination.links.slice(1, -1).map((link, index) => {
                                            const pageNumber = parseInt(link.label.replace(/&[^;]+;/g, ''));
                                            const isEllipsis = link.label.includes('...');

                                            if (isEllipsis) {
                                                return (
                                                    <span
                                                        key={index}
                                                        className="px-3 py-2 text-sm text-muted-foreground"
                                                    >
                                                        ...
                                                    </span>
                                                );
                                            }

                                            if (onPageChange) {
                                                return (
                                                    <Button
                                                        key={index}
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => onPageChange(pageNumber)}
                                                        disabled={!link.url}
                                                        className={cn(
                                                            'min-w-[36px] h-9 px-3',
                                                            link.active
                                                                ? 'text-primary font-bold bg-muted'
                                                                : 'text-muted-foreground hover:text-foreground hover:bg-muted',
                                                            !link.url && 'opacity-40 cursor-not-allowed'
                                                        )}
                                                    >
                                                        {pageNumber}
                                                    </Button>
                                                );
                                            }

                                            return (
                                                <Link
                                                    key={index}
                                                    href={link.url || '#'}
                                                    className={cn(
                                                        'inline-flex items-center justify-center min-w-[36px] h-9 px-3 rounded-md text-sm font-medium transition-all duration-200',
                                                        link.active
                                                            ? 'text-primary font-bold'
                                                            : 'text-muted-foreground hover:text-foreground hover:bg-muted',
                                                        !link.url && 'pointer-events-none opacity-40'
                                                    )}
                                                >
                                                    {pageNumber}
                                                </Link>
                                            );
                                        })
                                    )}
                                </div>

                                {/* Next */}
                                {onPageChange ? (
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => onPageChange(Math.min(pagination.last_page, pagination.current_page + 1))}
                                        disabled={!pagination.links[pagination.links.length - 1]?.url}
                                        className={cn(
                                            'w-9 h-9 p-0',
                                            !pagination.links[pagination.links.length - 1]?.url && 'opacity-40 cursor-not-allowed'
                                        )}
                                    >
                                        <ChevronRight className="w-5 h-5" />
                                    </Button>
                                ) : (
                                    <Link
                                        href={pagination.links[pagination.links.length - 1]?.url || '#'}
                                        className={cn(
                                            'inline-flex items-center justify-center w-9 h-9 rounded-md text-sm font-medium transition-all duration-200',
                                            pagination.links[pagination.links.length - 1]?.url
                                                ? 'text-foreground hover:text-foreground hover:bg-muted'
                                                : 'text-muted-foreground pointer-events-none opacity-40'
                                        )}
                                    >
                                        <ChevronRight className="w-5 h-5" />
                                    </Link>
                                )}

                                {/* Last */}
                                {onPageChange ? (
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => onPageChange(pagination.last_page)}
                                        disabled={pagination.current_page >= pagination.last_page}
                                        className={cn(
                                            'w-9 h-9 p-0',
                                            pagination.current_page >= pagination.last_page && 'opacity-40 cursor-not-allowed'
                                        )}
                                    >
                                        <ChevronLast className="w-5 h-5" />
                                    </Button>
                                ) : (
                                    <Link
                                        href={pagination.links[pagination.links.length - 2]?.url || '#'}
                                        className={cn(
                                            'inline-flex items-center justify-center w-9 h-9 rounded-md text-sm font-medium transition-all duration-200',
                                            pagination.current_page < pagination.last_page
                                                ? 'text-foreground hover:text-foreground hover:bg-muted'
                                                : 'text-muted-foreground pointer-events-none opacity-40'
                                        )}
                                    >
                                        <ChevronLast className="w-5 h-5" />
                                    </Link>
                                )}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}

// Utility hook for debounced search
export function useDebouncedValue<T>(value: T, delay: number): T {
    const [debouncedValue, setDebouncedValue] = React.useState(value);

    React.useEffect(() => {
        const timer = setTimeout(() => {
            setDebouncedValue(value);
        }, delay);

        return () => clearTimeout(timer);
    }, [value, delay]);

    return debouncedValue;
}

// Predefined cell renderers
export const DataTableCells = {
    // Text cell with optional truncation
    text: (value: string | null | undefined, options?: { truncate?: number; className?: string }) => {
        const text = value || '-';
        const display = options?.truncate && text.length > options.truncate
            ? `${text.slice(0, options.truncate)}...`
            : text;
        return <span className={cn('text-foreground', options?.className)}>{display}</span>;
    },

    // Email cell
    email: (value: string) => (
        <span className="text-muted-foreground">{value}</span>
    ),

    // Badge cell for roles/status
    badge: (values: Array<{ id: number | string; name: string }>, options?: { variant?: 'default' | 'secondary' | 'destructive' | 'outline' }) => (
        <div className="flex flex-wrap gap-1">
            {values.map((item) => (
                <Badge
                    key={item.id}
                    variant={options?.variant || 'secondary'}
                    className="text-xs px-2 py-1 font-medium"
                >
                    {item.name.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase())}
                </Badge>
            ))}
        </div>
    ),

    // Single badge cell
    singleBadge: (value: string, options?: { variant?: 'default' | 'secondary' | 'destructive' | 'outline'; className?: string }) => (
        <Badge variant={options?.variant || 'secondary'} className={cn('text-xs px-2 py-1 font-medium', options?.className)}>
            {value}
        </Badge>
    ),

    // Number cell with formatting
    number: (value: number, options?: { prefix?: string; suffix?: string; decimals?: number }) => {
        const formatted = value.toLocaleString(undefined, {
            minimumFractionDigits: options?.decimals || 0,
            maximumFractionDigits: options?.decimals || 0,
        });
        return (
            <span className="tabular-nums text-foreground">
                {options?.prefix}{formatted}{options?.suffix}
            </span>
        );
    },

    // Currency cell
    currency: (value: number, currency = 'USD') => {
        const formatted = new Intl.NumberFormat(undefined, {
            style: 'currency',
            currency,
        }).format(value);
        return <span className="tabular-nums font-medium text-foreground">{formatted}</span>;
    },

    // Date cell
    date: (value: string | Date, options?: { format?: 'short' | 'long' | 'relative' }) => {
        const date = new Date(value);
        let formatted: string;

        switch (options?.format) {
            case 'long':
                formatted = date.toLocaleDateString(undefined, {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                });
                break;
            case 'relative':
                // Simple relative formatting
                const now = new Date();
                const diff = now.getTime() - date.getTime();
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                if (days === 0) formatted = 'Today';
                else if (days === 1) formatted = 'Yesterday';
                else if (days < 7) formatted = `${days} days ago`;
                else formatted = date.toLocaleDateString();
                break;
            case 'short':
            default:
                formatted = date.toLocaleDateString(undefined, {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                });
        }

        return <span className="text-muted-foreground">{formatted}</span>;
    },

    // Status indicator cell
    status: (status: string, options?: { 
        active?: string[]; 
        inactive?: string[];
        pending?: string[];
    }) => {
        const statusLower = status.toLowerCase();
        const activeStates = options?.active || ['active', 'enabled', 'published', 'completed'];
        const inactiveStates = options?.inactive || ['inactive', 'disabled', 'draft', 'archived'];
        const pendingStates = options?.pending || ['pending', 'processing', 'waiting'];

        let variant: 'default' | 'secondary' | 'destructive' | 'outline' = 'secondary';
        if (activeStates.includes(statusLower)) variant = 'default';
        else if (inactiveStates.includes(statusLower)) variant = 'destructive';
        else if (pendingStates.includes(statusLower)) variant = 'outline';

        return (
            <Badge variant={variant} className="text-xs px-2 py-1 font-medium">
                {status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase())}
            </Badge>
        );
    },
};

// Export default
export default DataTable;
