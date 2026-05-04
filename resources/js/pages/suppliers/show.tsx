import { Head, Link } from '@inertiajs/react';
import { Truck, ArrowLeft, MapPin, Phone, Mail, User, FileText, Calendar, Edit, Package } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { index as suppliersIndex, edit as suppliersEdit } from '@/routes/suppliers';
import type { SupplierShowProps } from '@/types';

export default function Show({ supplier }: SupplierShowProps) {
    return (
        <>
            <Head title={supplier.name} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6 max-w-4xl mx-auto">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href={suppliersIndex()}>
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div className="flex items-center gap-2">
                            <Truck className="h-6 w-6 text-primary" />
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight">{supplier.name}</h1>
                                <p className="text-sm text-muted-foreground">{supplier.supplier_code}</p>
                            </div>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Link href={suppliersEdit(supplier.id)}>
                            <Button>
                                <Edit className="h-4 w-4 mr-2" />
                                Edit Supplier
                            </Button>
                        </Link>
                    </div>
                </div>

                <div className="flex flex-wrap gap-2">
                    {supplier.is_active ? (
                        <Badge variant="default" className="text-sm px-3 py-1">Active</Badge>
                    ) : (
                        <Badge variant="destructive" className="text-sm px-3 py-1">Inactive</Badge>
                    )}
                    {supplier.payment_terms !== null && supplier.payment_terms !== undefined && (
                        <Badge variant="outline" className="text-sm px-3 py-1">
                            {supplier.payment_terms === 0 ? 'Immediate' : `Net ${supplier.payment_terms}`}
                        </Badge>
                    )}
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <User className="h-5 w-5" />
                                Contact Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Contact Person</p>
                                <p className="text-sm">{supplier.contact_person || 'Not specified'}</p>
                            </div>
                            <Separator />
                            <div className="space-y-3">
                                {supplier.phone && (
                                    <div className="flex items-center gap-2">
                                        <Phone className="h-4 w-4 text-muted-foreground" />
                                        <span className="text-sm">{supplier.phone}</span>
                                    </div>
                                )}
                                {supplier.email && (
                                    <div className="flex items-center gap-2">
                                        <Mail className="h-4 w-4 text-muted-foreground" />
                                        <a href={`mailto:${supplier.email}`} className="text-sm hover:underline">
                                            {supplier.email}
                                        </a>
                                    </div>
                                )}
                                {!supplier.phone && !supplier.email && (
                                    <p className="text-sm text-muted-foreground">No contact information provided</p>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <MapPin className="h-5 w-5" />
                                Address
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {supplier.address && (
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Street Address</p>
                                    <p className="text-sm">{supplier.address}</p>
                                </div>
                            )}
                            {supplier.city && (
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">City</p>
                                    <p className="text-sm">{supplier.city}</p>
                                </div>
                            )}
                            {!supplier.address && !supplier.city && (
                                <p className="text-sm text-muted-foreground">No address provided</p>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Package className="h-5 w-5" />
                                Business Details
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Payment Terms</p>
                                <p className="text-sm">
                                    {supplier.payment_terms === 0
                                        ? 'Immediate'
                                        : supplier.payment_terms
                                            ? `Net ${supplier.payment_terms}`
                                            : 'Not specified'}
                                </p>
                            </div>
                            <Separator />
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Created</p>
                                    <p className="text-sm">{new Date(supplier.created_at).toLocaleDateString()}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Updated</p>
                                    <p className="text-sm">{new Date(supplier.updated_at).toLocaleDateString()}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileText className="h-5 w-5" />
                                Notes
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {supplier.notes ? (
                                <p className="text-sm whitespace-pre-wrap">{supplier.notes}</p>
                            ) : (
                                <p className="text-sm text-muted-foreground">No notes added</p>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
