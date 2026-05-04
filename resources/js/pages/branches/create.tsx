import { Head, Link, useForm } from '@inertiajs/react';
import { Building2, ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { index as branchesIndex, store as branchesStore } from '@/routes/branches';
import type { BranchFormProps } from '@/types';

export default function Create({ managers }: BranchFormProps) {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        name: '',
        address: '',
        city: '',
        phone: '',
        email: '',
        manager_id: '',
        is_active: true,
        is_main_branch: false,
        timezone: '',
        currency: '',
        tax_rate: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(branchesStore.url());
    };

    return (
        <>
            <Head title="Create Branch" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6 max-w-4xl mx-auto">
                <div className="flex items-center gap-4">
                    <Link href={branchesIndex()}>
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div className="flex items-center gap-2">
                        <Building2 className="h-6 w-6 text-primary" />
                        <h1 className="text-2xl font-bold tracking-tight">Create Branch</h1>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>Enter the basic details for this branch.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="code">Branch Code *</Label>
                                    <Input
                                        id="code"
                                        value={data.code}
                                        onChange={(e) => setData('code', e.target.value)}
                                        placeholder="e.g., HQ001"
                                    />
                                    {errors.code && <p className="text-sm text-destructive">{errors.code}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="name">Branch Name *</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="e.g., Main Headquarters"
                                    />
                                    {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="address">Address</Label>
                                <Input
                                    id="address"
                                    value={data.address}
                                    onChange={(e) => setData('address', e.target.value)}
                                    placeholder="Full address"
                                />
                                {errors.address && <p className="text-sm text-destructive">{errors.address}</p>}
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="city">City</Label>
                                    <Input
                                        id="city"
                                        value={data.city}
                                        onChange={(e) => setData('city', e.target.value)}
                                        placeholder="e.g., New York"
                                    />
                                    {errors.city && <p className="text-sm text-destructive">{errors.city}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="phone">Phone</Label>
                                    <Input
                                        id="phone"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                        placeholder="e.g., +1 (555) 123-4567"
                                    />
                                    {errors.phone && <p className="text-sm text-destructive">{errors.phone}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        placeholder="branch@company.com"
                                    />
                                    {errors.email && <p className="text-sm text-destructive">{errors.email}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="manager">Manager</Label>
                                    <Select
                                        value={data.manager_id}
                                        onValueChange={(value) => setData('manager_id', value)}
                                    >
                                        <SelectTrigger id="manager">
                                            <SelectValue placeholder="Select a manager" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {managers.map((manager) => (
                                                <SelectItem key={manager.id} value={String(manager.id)}>
                                                    {manager.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.manager_id && <p className="text-sm text-destructive">{errors.manager_id}</p>}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Settings</CardTitle>
                            <CardDescription>Configure additional branch settings.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="timezone">Timezone</Label>
                                    <Input
                                        id="timezone"
                                        value={data.timezone}
                                        onChange={(e) => setData('timezone', e.target.value)}
                                        placeholder="e.g., America/New_York"
                                    />
                                    {errors.timezone && <p className="text-sm text-destructive">{errors.timezone}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="currency">Currency</Label>
                                    <Input
                                        id="currency"
                                        value={data.currency}
                                        onChange={(e) => setData('currency', e.target.value)}
                                        placeholder="e.g., USD"
                                    />
                                    {errors.currency && <p className="text-sm text-destructive">{errors.currency}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="tax_rate">Tax Rate (%)</Label>
                                    <Input
                                        id="tax_rate"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        value={data.tax_rate}
                                        onChange={(e) => setData('tax_rate', e.target.value)}
                                        placeholder="0.00"
                                    />
                                    {errors.tax_rate && <p className="text-sm text-destructive">{errors.tax_rate}</p>}
                                </div>
                            </div>

                            <div className="flex flex-wrap gap-6 pt-2">
                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_active"
                                        checked={data.is_active}
                                        onCheckedChange={(checked) => setData('is_active', checked as boolean)}
                                    />
                                    <Label htmlFor="is_active" className="font-normal cursor-pointer">
                                        Active
                                    </Label>
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_main_branch"
                                        checked={data.is_main_branch}
                                        onCheckedChange={(checked) => setData('is_main_branch', checked as boolean)}
                                    />
                                    <Label htmlFor="is_main_branch" className="font-normal cursor-pointer">
                                        Main Branch
                                    </Label>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Creating...' : 'Create Branch'}
                        </Button>
                        <Link href={branchesIndex()}>
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
