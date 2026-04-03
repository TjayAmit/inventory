import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Search, Plus, Edit, Eye, Trash2, ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
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
import AppContentWrapper from '@/components/app-content-wrapper';
import { DeleteConfirmDialog } from '@/components/delete-confirm-dialog';

interface User {
    id: number;
    name: string;
    email: string;
    roles: Array<{
        id: number;
        name: string;
    }>;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface UsersProps {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginationLink[];
    };
    filters: {
        search?: string;
        role?: string;
    };
    roles: Array<{
        id: number;
        name: string;
    }>;
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
            roles: Array<{
                id: number;
                name: string;
            }>;
        };
    };
}

export default function UsersIndex({ users, filters, roles, auth }: UsersProps) {
    const [search, setSearch] = React.useState(filters.search || '');
    const [roleFilter, setRoleFilter] = React.useState(filters.role || '');

    const currentUser = auth.user;
    const isAdmin = (user: User) => user.roles.some(role => role.name === 'admin');
    const canDelete = (user: User) => !isAdmin(user);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/users', { search, role: roleFilter }, { preserveState: true });
    };

    const [deleteDialog, setDeleteDialog] = useState<{
        isOpen: boolean;
        user: User | null;
    }>({ isOpen: false, user: null });

    const openDeleteDialog = (user: User) => {
        setDeleteDialog({ isOpen: true, user });
    };

    const closeDeleteDialog = () => {
        setDeleteDialog({ isOpen: false, user: null });
    };

    const confirmDelete = () => {
        if (deleteDialog.user) {
            router.delete(`/users/${deleteDialog.user.id}`);
            closeDeleteDialog();
        }
    };

    return (
        <> 
            <Head title="Users" />
            <AppContentWrapper>
                {/* Page Header - Outside Card */}
                <div className="flex justify-between items-center mb-4">
                    <h1 className="text-3xl font-bold tracking-tight">Users</h1>
                    <Link href="/users/create">
                        <Button>
                            <Plus className="w-4 h-4 mr-2" />
                            Add User
                        </Button>
                    </Link>
                </div>

                {/* Card Content */}
                <div className="bg-card text-card-foreground overflow-hidden shadow-sm sm:rounded-lg border">
                    <div className="p-6">
                        {/* Filters */}
                        <form onSubmit={handleSearch} className="mb-6">
                            <div className="flex justify-between items-center gap-4">
                                <div className="text-sm text-muted-foreground">
                                    Manage user accounts, roles and permissions
                                </div>
                                <div className="flex gap-3 items-center">
                                    <div className="relative w-[280px]">
                                        <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
                                        <Input
                                            type="text"
                                            value={search}
                                            onChange={(e) => setSearch(e.target.value)}
                                            placeholder="Search users..."
                                            className="pl-10"
                                        />
                                    </div>
                                    <Select
                                        value={roleFilter || undefined}
                                        onValueChange={(value) => setRoleFilter(value === 'all' ? '' : value)}
                                    >
                                        <SelectTrigger className="w-[160px]">
                                            <SelectValue placeholder="All Roles" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Roles</SelectItem>
                                            {roles.map((role) => (
                                                <SelectItem key={role.id} value={role.name}>
                                                    {role.name.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <Button type="submit" variant="secondary">
                                        Search
                                    </Button>
                                </div>
                            </div>
                        </form>

                        {/* Users Table */}
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-[#242427] hover:bg-[#242427]">
                                    <TableHead>Name</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Roles</TableHead>
                                    <TableHead className="w-[100px]">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {users.data.map((user) => (
                                    <TableRow key={user.id}>
                                        <TableCell className="font-medium">{user.name}</TableCell>
                                        <TableCell className="text-muted-foreground">{user.email}</TableCell>
                                        <TableCell>
                                            <div className="flex flex-wrap gap-1">
                                                {user.roles.map((role) => (
                                                    <Badge key={role.id} variant="secondary">
                                                        {role.name.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                                    </Badge>
                                                ))}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Link href={`/users/${user.id}`}>
                                                    <Button variant="ghost" size="icon">
                                                        <Eye className="w-4 h-4" />
                                                    </Button>
                                                </Link>
                                                <Link href={`/users/${user.id}/edit`}>
                                                    <Button variant="ghost" size="icon">
                                                        <Edit className="w-4 h-4" />
                                                    </Button>
                                                </Link>
                                                {canDelete(user) ? (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={() => openDeleteDialog(user)}
                                                        className="text-destructive hover:text-destructive"
                                                    >
                                                        <Trash2 className="w-4 h-4" />
                                                    </Button>
                                                ) : (
                                                    <TooltipProvider>
                                                        <Tooltip>
                                                            <TooltipTrigger asChild>
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    disabled
                                                                    className="opacity-50 cursor-not-allowed"
                                                                >
                                                                    <Trash2 className="w-4 h-4" />
                                                                </Button>
                                                            </TooltipTrigger>
                                                            <TooltipContent>
                                                                <p>Admin users cannot be deleted</p>
                                                            </TooltipContent>
                                                        </Tooltip>
                                                    </TooltipProvider>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {/* Pagination */}
                        <div className="mt-6">
                            <div className="flex justify-between items-center">
                                <div className="text-sm text-muted-foreground">
                                    Page {users.current_page} of {users.last_page}
                                </div>
                                <div className="flex gap-1 items-center">
                                    {/* Previous */}
                                    <Link
                                        href={users.links[0]?.url || '#'}
                                        className={cn(
                                            "p-2 rounded-md border transition-colors flex items-center justify-center",
                                            users.links[0]?.url
                                                ? "bg-background text-foreground border-input hover:bg-accent"
                                                : "opacity-50 cursor-not-allowed pointer-events-none"
                                        )}
                                    >
                                        <ChevronLeft className="w-4 h-4" />
                                    </Link>

                                    {/* Page Numbers */}
                                    {users.links.slice(1, -1).map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            className={cn(
                                                "min-w-[36px] h-[36px] px-3 py-1 text-sm rounded-md border transition-colors flex items-center justify-center",
                                                link.active
                                                    ? "bg-primary text-primary-foreground border-primary"
                                                    : "bg-background text-foreground border-input hover:bg-accent",
                                                !link.url && "opacity-50 cursor-not-allowed pointer-events-none"
                                            )}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}

                                    {/* Next */}
                                    <Link
                                        href={users.links[users.links.length - 1]?.url || '#'}
                                        className={cn(
                                            "p-2 rounded-md border transition-colors flex items-center justify-center",
                                            users.links[users.links.length - 1]?.url
                                                ? "bg-background text-foreground border-input hover:bg-accent"
                                                : "opacity-50 cursor-not-allowed pointer-events-none"
                                        )}
                                    >
                                        <ChevronRight className="w-4 h-4" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </AppContentWrapper>

            <DeleteConfirmDialog
                isOpen={deleteDialog.isOpen}
                onClose={closeDeleteDialog}
                onConfirm={confirmDelete}
                itemName={deleteDialog.user?.name}
                description="This action cannot be undone. The user will be permanently removed from the system."
            />
        </>
    );
}


UsersIndex.layout = {
    breadcrumbs: [
        {
            title: 'Users',
            href: '/users',
        },
    ],
};