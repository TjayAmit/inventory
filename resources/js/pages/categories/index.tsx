import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Plus, Edit, Eye, Trash2, Folder, FolderOpen, ToggleLeft, ToggleRight, ArrowUpDown } from 'lucide-react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { DataTable, DataTableColumn, DataTableAction, useDebouncedValue, DataTableCells } from '@/components/data-table';
import { DeleteConfirmDialog } from '@/components/delete-confirm-dialog';
import { Badge } from '@/components/ui/badge';
import AppContentWrapper from '@/components/app-content-wrapper';

interface Category {
    id: number;
    name: string;
    description: string | null;
    parentId: number | null;
    parentName: string | null;
    isActive: boolean;
    sortOrder: number;
    productsCount: number | null;
    fullPath: string | null;
    hasChildren: boolean;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface CategoriesProps {
    categories: {
        data: Category[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginationLink[];
    };
    filters: {
        search?: string;
        status?: string;
    };
    can: {
        create: boolean;
        edit: boolean;
        delete: boolean;
        manage: boolean;
    };
}

export default function CategoriesIndex({ categories, filters, can }: CategoriesProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');
    const debouncedSearch = useDebouncedValue(search, 500);

    // Apply filters when they change
    React.useEffect(() => {
        router.get('/categories', { 
            search: debouncedSearch, 
            status: statusFilter 
        }, { preserveState: true, replace: true });
    }, [debouncedSearch, statusFilter]);

    const [deleteDialog, setDeleteDialog] = useState<{
        isOpen: boolean;
        category: Category | null;
    }>({ isOpen: false, category: null });

    const openDeleteDialog = (category: Category) => {
        setDeleteDialog({ isOpen: true, category });
    };

    const closeDeleteDialog = () => {
        setDeleteDialog({ isOpen: false, category: null });
    };

    const confirmDelete = () => {
        if (deleteDialog.category) {
            router.delete(`/categories/${deleteDialog.category.id}`);
            closeDeleteDialog();
        }
    };

    const toggleStatus = (category: Category) => {
        router.put(`/categories/${category.id}/toggle-status`, {}, { preserveState: true });
    };

    // Define columns for the categories table
    const columns: DataTableColumn<Category>[] = [
        {
            key: 'name',
            header: 'Name',
            cell: (category) => (
                <div className="flex items-center gap-2">
                    {category.hasChildren ? (
                        <FolderOpen className="w-4 h-4 text-muted-foreground" />
                    ) : (
                        <Folder className="w-4 h-4 text-muted-foreground" />
                    )}
                    <div className="flex flex-col">
                        <span className="font-medium text-foreground">{category.name}</span>
                        {category.parentName && (
                            <span className="text-xs text-muted-foreground">
                                Parent: {category.parentName}
                            </span>
                        )}
                    </div>
                </div>
            ),
        },
        {
            key: 'fullPath',
            header: 'Path',
            cell: (category) => (
                <span className="text-sm text-muted-foreground truncate max-w-xs block">
                    {category.fullPath || category.name}
                </span>
            ),
        },
        {
            key: 'productsCount',
            header: 'Products',
            cell: (category) => (
                <span className="text-muted-foreground">
                    {category.productsCount ?? 0} products
                </span>
            ),
        },
        {
            key: 'isActive',
            header: 'Status',
            cell: (category) => (
                <Badge variant={category.isActive ? 'default' : 'secondary'}>
                    {category.isActive ? 'Active' : 'Inactive'}
                </Badge>
            ),
        },
        {
            key: 'sortOrder',
            header: 'Sort Order',
            cell: (category) => (
                <span className="text-muted-foreground">{category.sortOrder}</span>
            ),
        },
    ];

    // Define actions for the categories table
    const actions: DataTableAction<Category>[] = [
        {
            key: 'view',
            icon: <Eye className="w-4 h-4" />,
            label: 'View',
            href: (category) => `/categories/${category.id}`,
            variant: 'ghost',
        },
        {
            key: 'edit',
            icon: <Edit className="w-4 h-4" />,
            label: 'Edit',
            href: (category) => `/categories/${category.id}/edit`,
            variant: 'ghost',
            visible: () => can.edit,
        },
        {
            key: 'toggle',
            icon: <ToggleRight className="w-4 h-4" />,
            label: 'Toggle Status',
            onClick: toggleStatus,
            variant: 'ghost',
            visible: () => can.manage,
        },
        {
            key: 'delete',
            icon: <Trash2 className="w-4 h-4" />,
            label: 'Delete',
            onClick: openDeleteDialog,
            variant: 'ghost',
            visible: (category) => can.delete && !category.hasChildren && (category.productsCount === 0 || category.productsCount === null),
            disabled: (category) => category.hasChildren || (category.productsCount !== null && category.productsCount > 0),
            tooltip: (category) => {
                if (category.hasChildren) return 'Cannot delete category with subcategories';
                if (category.productsCount && category.productsCount > 0) return 'Cannot delete category with products';
                return null;
            },
        },
    ];

    // Status filter component
    const statusFilterComponent = (
        <Select
            value={statusFilter || undefined}
            onValueChange={(value) => setStatusFilter(value === 'all' ? '' : value)}
        >
            <SelectTrigger className="w-36 h-10 bg-muted/50 border-border hover:bg-muted focus:bg-background focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-lg transition-all duration-200">
                <SelectValue placeholder="Filter by status" />
            </SelectTrigger>
            <SelectContent className="bg-popover border-border shadow-lg rounded-lg">
                <SelectItem value="all" className="hover:bg-muted/50 cursor-pointer">
                    All Status
                </SelectItem>
                <SelectItem value="active" className="hover:bg-muted/50 cursor-pointer">
                    Active
                </SelectItem>
                <SelectItem value="inactive" className="hover:bg-muted/50 cursor-pointer">
                    Inactive
                </SelectItem>
            </SelectContent>
        </Select>
    );

    return (
        <>
            <Head title="Categories" />
            <AppContentWrapper>
                <DataTable
                    title="Categories"
                    description="Manage product categories and organization"
                    data={categories.data}
                    columns={columns}
                    pagination={{
                        current_page: categories.current_page,
                        last_page: categories.last_page,
                        per_page: categories.per_page,
                        total: categories.total,
                        links: categories.links,
                    }}
                    searchValue={search}
                    onSearchChange={setSearch}
                    searchPlaceholder="Search categories..."
                    filters={statusFilterComponent}
                    actions={actions}
                    createHref={can.create ? "/categories/create" : undefined}
                    createLabel="Add Category"
                    emptyTitle="No categories found"
                    emptyDescription="Get started by creating a new category."
                />
            </AppContentWrapper>

            <DeleteConfirmDialog
                isOpen={deleteDialog.isOpen}
                onClose={closeDeleteDialog}
                onConfirm={confirmDelete}
                itemName={deleteDialog.category?.name}
                description="This action cannot be undone. The category will be permanently removed from the system."
            />
        </>
    );
}

CategoriesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Categories',
            href: '/categories',
        },
    ],
};
