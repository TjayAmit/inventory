import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Search, Plus, Edit, Eye, Trash2 } from 'lucide-react';
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
import { cn } from '@/lib/utils';
import AppContentWrapper from '@/components/app-content-wrapper';

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
}

export default function UsersIndex({ users, filters, roles }: UsersProps) {
    const [search, setSearch] = React.useState(filters.search || '');
    const [roleFilter, setRoleFilter] = React.useState(filters.role || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/users', { search, role: roleFilter }, { preserveState: true });
    };

    const deleteUser = (userId: number) => {
        if (confirm('Are you sure you want to delete this user?')) {
            router.delete(`/users/${userId}`);
        }
    };

    return (
        <> 
            <Head title="Users" />
            <AppContentWrapper>
                <div className="bg-card text-card-foreground overflow-hidden shadow-sm sm:rounded-lg border">
                    <div className="p-6 border-b">
                        <div className="flex justify-between items-center mb-6">
                            <h1 className="text-2xl font-semibold">Users</h1>
                            <Link href="/users/create">
                                <Button>
                                    <Plus className="w-4 h-4 mr-2" />
                                    Add User
                                </Button>
                            </Link>
                        </div>

                        {/* Filters */}
                        <form onSubmit={handleSearch} className="mb-6">
                            <div className="flex gap-4">
                                <div className="flex-1 relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
                                    <Input
                                        type="text"
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        placeholder="Search users..."
                                        className="pl-10"
                                    />
                                </div>
                                <div>
                                    <Select
                                        value={roleFilter || undefined}
                                        onValueChange={(value) => setRoleFilter(value === 'all' ? '' : value)}
                                    >
                                        <SelectTrigger className="w-[180px]">
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
                                </div>
                                <Button type="submit" variant="secondary">
                                    Search
                                </Button>
                            </div>
                        </form>

                        {/* Users Table */}
                        <Table>
                            <TableHeader>
                                <TableRow>
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
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => deleteUser(user.id)}
                                                    className="text-destructive hover:text-destructive"
                                                >
                                                    <Trash2 className="w-4 h-4" />
                                                </Button>
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
                                    Showing {users.data.length} of {users.total} results
                                </div>
                                <div className="flex gap-2">
                                    {users.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            className={cn(
                                                "px-3 py-1 text-sm rounded-md border transition-colors",
                                                link.active
                                                    ? "bg-primary text-primary-foreground border-primary"
                                                    : "bg-background text-foreground border-input hover:bg-accent",
                                                !link.url && "opacity-50 cursor-not-allowed pointer-events-none"
                                            )}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </AppContentWrapper>
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