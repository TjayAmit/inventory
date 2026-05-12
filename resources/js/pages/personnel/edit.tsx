import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, UserCog } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { index as personnelIndex, update as personnelUpdate } from '@/routes/personnel';
import type { PersonnelEditProps } from '@/types';
import AppLayout from '@/layouts/app-layout';

const ROLE_LABELS: Record<string, string> = {
    admin: 'Admin',
    owner: 'Owner',
    store_manager: 'Store Manager',
    cashier: 'Cashier',
    warehouse_staff: 'Warehouse Staff',
};

const MODULE_TITLE = 'Edit Staff';

export default function Edit({ user, branches, roles }: PersonnelEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        name: user.name,
        email: user.email,
        phone: user.phone ?? '',
        branch_id: user.branch_id?.toString() ?? '',
        role: user.roles[0]?.name ?? '',
        is_active: user.is_active,
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(personnelUpdate.url(user.id));
    };

    return (
        <>
            <Head title={MODULE_TITLE} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
                <div className="flex items-center gap-4">
                    <Link href={personnelIndex()}>
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div className="flex items-center gap-2">
                        <UserCog className="h-6 w-6 text-primary" />
                        <h1 className="text-2xl font-bold tracking-tight">{MODULE_TITLE}</h1>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Personal Information</CardTitle>
                            <CardDescription>Update the staff member's basic details.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Full Name *</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="e.g., Juan dela Cruz"
                                    />
                                    {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="email">Email Address *</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        placeholder="staff@company.com"
                                    />
                                    {errors.email && <p className="text-sm text-destructive">{errors.email}</p>}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="phone">Phone Number</Label>
                                <Input
                                    id="phone"
                                    value={data.phone}
                                    onChange={(e) => setData('phone', e.target.value)}
                                    placeholder="e.g., +63 917 123 4567"
                                />
                                {errors.phone && <p className="text-sm text-destructive">{errors.phone}</p>}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Assignment</CardTitle>
                            <CardDescription>Update this staff member's branch and role.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="branch">Branch</Label>
                                    <Select
                                        value={data.branch_id}
                                        onValueChange={(v) => setData('branch_id', v)}
                                    >
                                        <SelectTrigger id="branch">
                                            <SelectValue placeholder="Select a branch" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {branches.map((b) => (
                                                <SelectItem key={b.id} value={String(b.id)}>
                                                    {b.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.branch_id && <p className="text-sm text-destructive">{errors.branch_id}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="role">Role</Label>
                                    <Select
                                        value={data.role}
                                        onValueChange={(v) => setData('role', v)}
                                    >
                                        <SelectTrigger id="role">
                                            <SelectValue placeholder="Select a role" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {roles.map((r) => (
                                                <SelectItem key={r} value={r}>
                                                    {ROLE_LABELS[r] ?? r}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.role && <p className="text-sm text-destructive">{errors.role}</p>}
                                </div>
                            </div>

                            <div className="flex items-center space-x-2 pt-1">
                                <Checkbox
                                    id="is_active"
                                    checked={data.is_active}
                                    onCheckedChange={(checked) => setData('is_active', checked as boolean)}
                                />
                                <Label htmlFor="is_active" className="font-normal cursor-pointer">
                                    Active
                                </Label>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Change Password</CardTitle>
                            <CardDescription>Leave blank to keep the current password.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="password">New Password</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        placeholder="Min. 8 characters"
                                    />
                                    {errors.password && <p className="text-sm text-destructive">{errors.password}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="password_confirmation">Confirm New Password</Label>
                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        placeholder="Re-enter new password"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Saving...' : 'Save Changes'}
                        </Button>
                        <Link href={personnelIndex()}>
                            <Button variant="outline" type="button">
                                Cancel
                            </Button>
                        </Link>
                    </div>
                </form>
            </div>
        </>
    );
}

Edit.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={[
        { title: 'Personnel', href: personnelIndex().url },
        { title: 'Edit Staff', href: '#' },
    ]}>
        {page}
    </AppLayout>
);
