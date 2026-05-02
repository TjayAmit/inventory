import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
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
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle>User Details</CardTitle>
                        <Link href="/users">
                            <Button variant="outline">
                                <ArrowLeft className="w-4 h-4 mr-2" />
                                Back to Users
                            </Button>
                        </Link>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div className="space-y-4">
                                <div>
                                    <Label className="text-muted-foreground">Name</Label>
                                    <p className="text-lg font-medium">{user.name}</p>
                                </div>
                                <div>
                                    <Label className="text-muted-foreground">Email</Label>
                                    <p className="text-lg font-medium">{user.email}</p>
                                </div>
                            </div>

                            <div>
                                <Label className="text-muted-foreground">Roles</Label>
                                <div className="flex flex-wrap gap-2 mt-1">
                                    {user.roles.map((role) => (
                                        <Badge key={role.id} variant="default">
                                            {role.name.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                        </Badge>
                                    ))}
                                </div>
                            </div>

                            <div>
                                <Label className="text-muted-foreground">Permissions</Label>
                                <div className="grid grid-cols-2 md:grid-cols-3 gap-2 mt-1">
                                    {user.permissions.map((permission) => (
                                        <Badge key={permission.id} variant="secondary">
                                            {permission.name.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                        </Badge>
                                    ))}
                                </div>
                            </div>

                            <div className="md:col-span-2 flex justify-end">
                                <Link href={`/users/${user.id}/edit`}>
                                    <Button>
                                        <Edit className="w-4 h-4 mr-2" />
                                        Edit User
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </CardContent>
                </Card>
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