import React from 'react';
import { Head, useForm, router } from '@inertiajs/react';
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

interface CreateProps {
    roles: Array<{
        id: number;
        name: string;
    }>;
}

export default function UsersCreate({ roles }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        roles: [] as string[],
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/users');
    };

    return (
        <>
            <Head title="Create User" />
            <AppContentWrapper>

                {/* Breadcrumbs */}
                <Breadcrumb className="mb-4">
                    <BreadcrumbList>
                        <BreadcrumbItem>
                            <BreadcrumbLink href="/users">Users</BreadcrumbLink>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator />
                        <BreadcrumbItem>
                            <BreadcrumbPage>Create</BreadcrumbPage>
                        </BreadcrumbItem>
                    </BreadcrumbList>
                </Breadcrumb>
                
                {/* Page Header */}
                <div className="mb-6">
                    <h1 className="text-2xl font-bold tracking-tight">Create User</h1>
                    <p className="text-muted-foreground mt-1">Add a new user to the system</p>
                </div>

                {/* Card Content - Compact Form */}
                <div className="max-w-2xl">
                    <Card>
                        <CardContent className="pt-6">
                            <form onSubmit={handleSubmit}>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="name">Name</Label>
                                        <Input
                                            id="name"
                                            type="text"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            required
                                            className="h-10"
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
                                            className="h-10"
                                        />
                                        {errors.email && (
                                            <p className="text-sm text-destructive">{errors.email}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="password">Password</Label>
                                        <Input
                                            id="password"
                                            type="password"
                                            value={data.password}
                                            onChange={(e) => setData('password', e.target.value)}
                                            required
                                            className="h-10"
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
                                            required
                                            className="h-10"
                                        />
                                        {errors.password_confirmation && (
                                            <p className="text-sm text-destructive">{errors.password_confirmation}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2 md:col-span-2">
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
                                </div>

                                <div className="flex justify-end gap-3 mt-6 pt-4 border-t border-border">
                                    <Button type="submit" disabled={processing}>
                                        Create User
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </AppContentWrapper>
        </>
    );
}

UsersCreate.layout = {
    breadcrumbs: [
        {
            title: 'Users',
            href: '/users',
        },
    ],
};