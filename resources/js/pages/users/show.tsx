import { Head, Link } from '@inertiajs/react';
import { Edit } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
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
    permissions: Array<{
        id: number;
        name: string;
    }>;
}

interface ShowProps {
    user: User;
}

export default function UsersShow({ user }: ShowProps) {
    return (
        <>
            <Head title={`User: ${user.name}`} />
            <AppContentWrapper>
                {/* Breadcrumbs */}
                <Breadcrumb className="mb-4">
                    <BreadcrumbList>
                        <BreadcrumbItem>
                            <BreadcrumbLink href="/users">Users</BreadcrumbLink>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator />
                        <BreadcrumbItem>
                            <BreadcrumbPage>{user.name}</BreadcrumbPage>
                        </BreadcrumbItem>
                    </BreadcrumbList>
                </Breadcrumb>

                {/* Page Header */}
                <div className="mb-6">
                    <h1 className="text-2xl font-bold tracking-tight">{user.name}</h1>
                    <p className="text-muted-foreground mt-1">{user.email}</p>
                </div>

                <div className="max-w-3xl">
                    <Card>
                        <CardHeader className="pb-4">
                            <CardTitle className="text-lg">User Details</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-4">
                                    <div>
                                        <Label className="text-muted-foreground text-sm">Name</Label>
                                        <p className="text-base font-medium">{user.name}</p>
                                    </div>
                                    <div>
                                        <Label className="text-muted-foreground text-sm">Email</Label>
                                        <p className="text-base font-medium">{user.email}</p>
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    <div>
                                        <Label className="text-muted-foreground text-sm">Roles</Label>
                                        <div className="flex flex-wrap gap-2 mt-1">
                                            {user.roles.map((role) => (
                                                <Badge key={role.id} variant="secondary" className="text-xs">
                                                    {role.name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                                </Badge>
                                            ))}
                                        </div>
                                    </div>

                                    <div>
                                        <Label className="text-muted-foreground text-sm">Permissions</Label>
                                        <div className="flex flex-wrap gap-2 mt-1">
                                            {user.permissions.length > 0 ? (
                                                user.permissions.map((permission) => (
                                                    <Badge key={permission.id} variant="outline" className="text-xs">
                                                        {permission.name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                                    </Badge>
                                                ))
                                            ) : (
                                                <span className="text-sm text-muted-foreground italic">No permissions assigned</span>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className="md:col-span-2 flex justify-end pt-4 border-t border-border">
                                    <Link href={`/users/${user.id}/edit`}>
                                        <Button size="sm">
                                            <Edit className="w-4 h-4 mr-2" />
                                            Edit User
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </AppContentWrapper>
        </>
    );
}

UsersShow.layout = {
    breadcrumbs: [
        {
            title: 'Users',
            href: '/users',
        },
    ],
};