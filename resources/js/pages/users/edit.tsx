import React from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { AutoSuggestion } from '@/components/ui/auto-suggestion';
import { Card, CardContent } from '@/components/ui/card';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
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

interface EditProps {
    user: User;
    roles: Array<{
        id: number;
        name: string;
    }>;
}

export default function UsersEdit({ user, roles }: EditProps) {
    const { data, setData, post, processing, errors } = useForm({
        name: user.name,
        email: user.email,
        password: '',
        password_confirmation: '',
        roles: user.roles?.map((role) => role.name) ?? [],
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        router.put(`/users/${user.id}`, data);
    };

    const handleDelete = () => {
        if (confirm(`Are you sure you want to delete ${user.name}? This action cannot be undone.`)) {
            const confirmation = prompt('Type "DELETE" to confirm:');
            if (confirmation === 'DELETE') {
                router.delete(`/users/${user.id}`);
            } else {
                alert('Delete cancelled. User was not deleted.');
            }
        }
    };

    return (
        <>
            <Head title={`Edit User: ${user.name}`} />
            <AppContentWrapper>
                {/* Page Header - Outside Card */}
                <div className="flex justify-between items-center mb-4">
                    <div className="flex items-center gap-4">
                        <Link href="/users">
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="w-4 h-4" />
                            </Button>
                        </Link>
                        <h1 className="text-3xl font-bold tracking-tight">Edit User</h1>
                    </div>
                    <Button variant="destructive" onClick={handleDelete}>
                        Delete User
                    </Button>
                </div>

                {/* Breadcrumbs */}
                <Breadcrumb className="mb-4">
                    <BreadcrumbList>
                        <BreadcrumbItem>
                            <BreadcrumbLink href="/users">Users</BreadcrumbLink>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator />
                        <BreadcrumbItem>
                            <BreadcrumbPage>Edit</BreadcrumbPage>
                        </BreadcrumbItem>
                    </BreadcrumbList>
                </Breadcrumb>

                {/* Card Content */}
                <Card>
                    <CardContent className="pt-6">
                        <form onSubmit={handleSubmit}>
                            <div className="space-y-6">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input
                                        id="name"
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                    />
                                    {errors.name && (
                                        <p className="text-sm text-destructive">{errors.name}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        required
                                    />
                                    {errors.email && (
                                        <p className="text-sm text-destructive">{errors.email}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="password">Password (leave empty to keep current)</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                    />
                                    {errors.password && (
                                        <p className="text-sm text-destructive">{errors.password}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="password_confirmation">Confirm Password</Label>
                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                    />
                                    {errors.password_confirmation && (
                                        <p className="text-sm text-destructive">{errors.password_confirmation}</p>
                                    )}
                                </div>

                                <div className="space-y-3">
                                    <Label>Roles</Label>
                                    <AutoSuggestion
                                        options={roles.map(role => ({
                                            value: role.name,
                                            label: role.name.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
                                        }))}
                                        selected={data.roles}
                                        onChange={(selected: string[]) => setData('roles', selected)}
                                        placeholder="Select roles..."
                                    />
                                    {errors.roles && (
                                        <p className="text-sm text-destructive">{errors.roles}</p>
                                    )}
                                </div>

                                <div className="flex justify-end">
                                    <Button type="submit" disabled={processing}>
                                        Update User
                                    </Button>
                                </div>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </AppContentWrapper>
        </>
    );
}

UsersEdit.layout = {
    breadcrumbs: [
        {
            title: 'Users',
            href: '/users',
        },
    ],
};
