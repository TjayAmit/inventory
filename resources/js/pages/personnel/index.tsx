import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Users, Building2, ShieldCheck, ShieldOff, UserMinus, UserPlus, Pencil, Trash2 } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { DataTable, DataTablePagination } from '@/components/data-table';
import { DeleteConfirmDialog } from '@/components/delete-confirm-dialog';
import {
    index as personnelIndex,
    create as personnelCreate,
    edit as personnelEdit,
    destroy as personnelDestroy,
    assignBranch,
    revokeBranch,
    assignRole,
    revokeRole,
} from '@/routes/personnel';
import type { PersonnelIndexProps, Personnel } from '@/types';
import AppLayout from '@/layouts/app-layout';

const MODULE_TITLE = 'Personnel';

const ROLE_LABELS: Record<string, string> = {
    admin: 'Admin',
    store_manager: 'Store Manager',
    cashier: 'Cashier',
    warehouse_staff: 'Warehouse Staff',
};

const ROLE_VARIANTS: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
    admin: 'destructive',
    store_manager: 'default',
    cashier: 'secondary',
    warehouse_staff: 'outline',
};

type AssignDialogState = {
    user: Personnel;
    mode: 'branch' | 'role';
} | null;

export default function Index({ data, branches, roles, filters }: PersonnelIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [perPage, setPerPage] = useState(filters.per_page || 10);
    const [branchFilter, setBranchFilter] = useState(filters.branch_id || '');
    const [roleFilter, setRoleFilter] = useState(filters.role || '');

    const [assignDialog, setAssignDialog] = useState<AssignDialogState>(null);
    const [revokeTarget, setRevokeTarget] = useState<Personnel | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<Personnel | null>(null);

    const { data: formData, setData, post, delete: del, processing, reset, errors } = useForm({
        branch_id: '',
        role: '',
    });

    const navigate = (params: Record<string, unknown> = {}) => {
        router.get(
            personnelIndex(),
            { search, per_page: perPage, branch_id: branchFilter, role: roleFilter, ...params },
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

    const handleBranchFilterChange = (value: string) => {
        const normalized = value === 'all' ? '' : value;
        setBranchFilter(normalized);
        navigate({ branch_id: normalized, page: 1 });
    };

    const handleRoleFilterChange = (value: string) => {
        const normalized = value === 'all' ? '' : value;
        setRoleFilter(normalized);
        navigate({ role: normalized, page: 1 });
    };

    const openAssignBranchDialog = (user: Personnel) => {
        reset();
        setData({
            branch_id: user.branch_id?.toString() ?? '',
            role: user.roles[0]?.name ?? '',
        });
        setAssignDialog({ user, mode: 'branch' });
    };

    const openAssignRoleDialog = (user: Personnel) => {
        reset();
        setData({
            branch_id: user.branch_id?.toString() ?? '',
            role: user.roles[0]?.name ?? '',
        });
        setAssignDialog({ user, mode: 'role' });
    };

    const handleAssignSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!assignDialog) return;

        const userId = assignDialog.user.id;

        if (assignDialog.mode === 'branch') {
            post(assignBranch.url(userId), {
                onSuccess: () => setAssignDialog(null),
            });
        } else {
            post(assignRole.url(userId), {
                onSuccess: () => setAssignDialog(null),
            });
        }
    };

    const handleRevoke = () => {
        if (!revokeTarget) return;
        del(revokeBranch.url(revokeTarget.id), {
            onFinish: () => setRevokeTarget(null),
        });
    };

    const handleDelete = () => {
        if (!deleteTarget) return;
        del(personnelDestroy.url(deleteTarget.id), {
            onFinish: () => setDeleteTarget(null),
        });
    };

    const handleRevokeRole = (user: Personnel) => {
        del(revokeRole.url(user.id));
    };

    const columns = [
        {
            key: 'name',
            header: 'Personnel',
            cell: (item: Personnel) => (
                <div>
                    <p className="text-sm font-medium text-foreground">{item.name}</p>
                    <p className="text-xs text-muted-foreground">{item.email}</p>
                </div>
            ),
        },
        {
            key: 'created_at',
            header: 'Registered At',
            cell: (item: Personnel) => (
                <span className="text-sm text-muted-foreground">
                    {new Date(item.created_at).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                    })}
                </span>
            ),
        },
        {
            key: 'roles',
            header: 'Role',
            cell: (item: Personnel) =>
                item.roles.length > 0 ? (
                    <div className="flex flex-wrap gap-1">
                        {item.roles.map((r) => (
                            <Badge key={r.id} variant={ROLE_VARIANTS[r.name] ?? 'outline'} className="text-xs">
                                {ROLE_LABELS[r.name] ?? r.name}
                            </Badge>
                        ))}
                    </div>
                ) : (
                    <span className="text-xs text-muted-foreground">No role</span>
                ),
        },
        {
            key: 'branch',
            header: 'Branch',
            cell: (item: Personnel) =>
                item.branch ? (
                    <div className="flex items-center gap-1">
                        <Building2 className="h-3 w-3 text-muted-foreground" />
                        <span className="text-sm text-foreground">{item.branch.name}</span>
                    </div>
                ) : (
                    <span className="text-xs text-muted-foreground">Unassigned</span>
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
            key: 'edit',
            icon: <Pencil className="w-4 h-4" />,
            label: 'Edit',
            onClick: (item: Personnel) => router.get(personnelEdit.url(item.id)),
        },
        {
            key: 'assign-branch',
            icon: <UserPlus className="w-4 h-4" />,
            label: 'Assign Branch',
            onClick: (item: Personnel) => openAssignBranchDialog(item),
            visible: (item: Personnel) => !item.branch_id,
        },
        {
            key: 'change-branch',
            icon: <Building2 className="w-4 h-4" />,
            label: 'Change Branch',
            onClick: (item: Personnel) => openAssignBranchDialog(item),
            visible: (item: Personnel) => !!item.branch_id,
        },
        {
            key: 'assign-role',
            icon: <ShieldCheck className="w-4 h-4" />,
            label: 'Change Role',
            onClick: (item: Personnel) => openAssignRoleDialog(item),
            visible: (item: Personnel) => !!item.branch_id,
        },
        {
            key: 'revoke-role',
            icon: <ShieldOff className="w-4 h-4" />,
            label: 'Revoke Role',
            onClick: (item: Personnel) => handleRevokeRole(item),
            visible: (item: Personnel) => !!item.branch_id && item.roles.length > 0,
            variant: 'ghost' as const,
        },
        {
            key: 'revoke-access',
            icon: <UserMinus className="w-4 h-4" />,
            label: 'Revoke Access',
            onClick: (item: Personnel) => setRevokeTarget(item),
            visible: (item: Personnel) => !!item.branch_id,
            variant: 'destructive' as const,
        },
        {
            key: 'delete',
            icon: <Trash2 className="w-4 h-4" />,
            label: 'Delete',
            onClick: (item: Personnel) => setDeleteTarget(item),
            variant: 'destructive' as const,
        },
    ];

    const customFilters = (
        <div className="flex items-center gap-2">
            <Select value={branchFilter || 'all'} onValueChange={handleBranchFilterChange}>
                <SelectTrigger className="w-40 h-9">
                    <SelectValue placeholder="All Branches" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All Branches</SelectItem>
                    {branches.map((b) => (
                        <SelectItem key={b.id} value={b.id.toString()}>
                            {b.name}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>

            <Select value={roleFilter || 'all'} onValueChange={handleRoleFilterChange}>
                <SelectTrigger className="w-36 h-9">
                    <SelectValue placeholder="All Roles" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All Roles</SelectItem>
                    {roles.map((r) => (
                        <SelectItem key={r} value={r}>
                            {ROLE_LABELS[r] ?? r}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );

    const isAssigningBranch = assignDialog?.mode === 'branch';

    return (
        <>
            <Head title={MODULE_TITLE} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
                <DataTable<Personnel>
                    title={MODULE_TITLE}
                    description="Manage personnel assignments across branches"
                    createHref={personnelCreate().url}
                    createLabel="New Staff"
                    data={data.data}
                    columns={columns}
                    pagination={pagination}
                    searchValue={search}
                    onSearchChange={handleSearchChange}
                    searchPlaceholder="Search by name or email..."
                    filters={customFilters}
                    actions={actions}
                    pageSizeOptions={[10, 25, 50, 100]}
                    onPageSizeChange={handlePerPageChange}
                    onPageChange={(page) => navigate({ page })}
                    emptyTitle="No personnel found"
                    emptyDescription={search ? 'Try a different search or clear the filter.' : undefined}
                />
            </div>

            {/* Assign Branch / Role Dialog */}
            <Dialog open={!!assignDialog} onOpenChange={(open) => !open && setAssignDialog(null)}>
                <DialogContent className="sm:max-w-md">
                    <form onSubmit={handleAssignSubmit}>
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2">
                                <Users className="h-5 w-5" />
                                {isAssigningBranch ? 'Assign to Branch' : 'Change Role'}
                            </DialogTitle>
                            <DialogDescription>
                                {isAssigningBranch
                                    ? `Assign ${assignDialog?.user.name} to a branch and set their role.`
                                    : `Update the role for ${assignDialog?.user.name}.`}
                            </DialogDescription>
                        </DialogHeader>

                        <div className="grid gap-4 py-4">
                            {isAssigningBranch && (
                                <div className="grid gap-2">
                                    <Label htmlFor="branch">Branch</Label>
                                    <Select
                                        value={formData.branch_id}
                                        onValueChange={(v) => setData('branch_id', v)}
                                    >
                                        <SelectTrigger id="branch" className="w-full">
                                            <SelectValue placeholder="Select branch..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {branches.map((b) => (
                                                <SelectItem key={b.id} value={b.id.toString()}>
                                                    {b.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.branch_id && (
                                        <p className="text-xs text-destructive">{errors.branch_id}</p>
                                    )}
                                </div>
                            )}

                            <div className="grid gap-2">
                                <Label htmlFor="role">Role</Label>
                                <Select
                                    value={formData.role}
                                    onValueChange={(v) => setData('role', v)}
                                >
                                    <SelectTrigger id="role" className="w-full">
                                        <SelectValue placeholder="Select role..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {roles.map((r) => (
                                            <SelectItem key={r} value={r}>
                                                {ROLE_LABELS[r] ?? r}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.role && (
                                    <p className="text-xs text-destructive">{errors.role}</p>
                                )}
                            </div>
                        </div>

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setAssignDialog(null)}
                                disabled={processing}
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing || (isAssigningBranch && !formData.branch_id)}>
                                {processing ? 'Saving...' : 'Save'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Revoke Access Confirmation */}
            <DeleteConfirmDialog
                isOpen={!!revokeTarget}
                onClose={() => setRevokeTarget(null)}
                onConfirm={handleRevoke}
                title="Revoke Branch Access"
                itemName={revokeTarget?.name}
                description="This will remove the user from their assigned branch and clear all their roles. They will lose access until reassigned."
            />

            {/* Delete Staff Confirmation */}
            <DeleteConfirmDialog
                isOpen={!!deleteTarget}
                onClose={() => setDeleteTarget(null)}
                onConfirm={handleDelete}
                title="Delete Staff Member"
                itemName={deleteTarget?.name}
                description="This will permanently delete this staff member and revoke all their roles and access. This action cannot be undone."
            />
        </>
    );
}

Index.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={[{ title: MODULE_TITLE, href: personnelIndex().url }]}>
        {page}
    </AppLayout>
);
