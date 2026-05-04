import { Head, Link } from '@inertiajs/react';
import { Building2, ArrowLeft, MapPin, Phone, Mail, User, Clock, DollarSign, Percent, Edit } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { index as branchesIndex, edit as branchesEdit } from '@/routes/branches';
import type { BranchShowProps } from '@/types';

export default function Show({ branch }: BranchShowProps) {
    return (
        <>
            <Head title={branch.name} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6 max-w-4xl mx-auto">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href={branchesIndex()}>
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div className="flex items-center gap-2">
                            <Building2 className="h-6 w-6 text-primary" />
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight">{branch.name}</h1>
                                <p className="text-sm text-muted-foreground">{branch.code}</p>
                            </div>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Link href={branchesEdit(branch.id)}>
                            <Button>
                                <Edit className="h-4 w-4 mr-2" />
                                Edit Branch
                            </Button>
                        </Link>
                    </div>
                </div>

                <div className="flex flex-wrap gap-2">
                    {branch.is_active ? (
                        <Badge variant="default" className="text-sm px-3 py-1">Active</Badge>
                    ) : (
                        <Badge variant="destructive" className="text-sm px-3 py-1">Inactive</Badge>
                    )}
                    {branch.is_main_branch && (
                        <Badge variant="outline" className="text-sm px-3 py-1">Main Branch</Badge>
                    )}
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <MapPin className="h-5 w-5" />
                                Location Details
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {branch.address && (
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Address</p>
                                    <p className="text-sm">{branch.address}</p>
                                </div>
                            )}
                            {branch.city && (
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">City</p>
                                    <p className="text-sm">{branch.city}</p>
                                </div>
                            )}
                            {(branch.address || branch.city) && <Separator />}
                            <div className="space-y-3">
                                {branch.phone && (
                                    <div className="flex items-center gap-2">
                                        <Phone className="h-4 w-4 text-muted-foreground" />
                                        <span className="text-sm">{branch.phone}</span>
                                    </div>
                                )}
                                {branch.email && (
                                    <div className="flex items-center gap-2">
                                        <Mail className="h-4 w-4 text-muted-foreground" />
                                        <a href={`mailto:${branch.email}`} className="text-sm hover:underline">
                                            {branch.email}
                                        </a>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <User className="h-5 w-5" />
                                Management
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Branch Manager</p>
                                <p className="text-sm">{branch.manager?.name || 'Not assigned'}</p>
                            </div>
                            <Separator />
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Created</p>
                                    <p className="text-sm">{new Date(branch.created_at).toLocaleDateString()}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Updated</p>
                                    <p className="text-sm">{new Date(branch.updated_at).toLocaleDateString()}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Clock className="h-5 w-5" />
                                Regional Settings
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Timezone</p>
                                    <p className="text-sm">{branch.timezone || 'Not set'}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Currency</p>
                                    <p className="text-sm">{branch.currency || 'Not set'}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Percent className="h-5 w-5" />
                                Tax Settings
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Tax Rate</p>
                                <p className="text-sm">{branch.tax_rate ? `${branch.tax_rate}%` : 'Not set'}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
