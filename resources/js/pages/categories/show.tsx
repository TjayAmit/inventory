import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Edit, Folder, FolderOpen, Package, ArrowLeft, FileText } from 'lucide-react';
import AppContentWrapper from '@/components/app-content-wrapper';

interface ChildCategory {
    id: number;
    name: string;
    isActive: boolean;
    sortOrder: number;
}

interface Category {
    id: number;
    name: string;
    description: string | null;
    parentId: number | null;
    parentName: string | null;
    isActive: boolean;
    sortOrder: number;
    productsCount: number | null;
    fullPath: string | null;
    hasChildren: boolean;
    createdAt: string;
    updatedAt: string;
}

interface ShowProps {
    category: Category;
    childCategories: ChildCategory[];
    can: {
        edit: boolean;
        delete: boolean;
        manage: boolean;
    };
}

export default function CategoriesShow({ category, childCategories, can }: ShowProps) {
    return (
        <>
            <Head title={category.name} />
            <AppContentWrapper>
                {/* Breadcrumbs */}
                <Breadcrumb className="mb-4">
                    <BreadcrumbList>
                        <BreadcrumbItem>
                            <BreadcrumbLink href="/categories">Categories</BreadcrumbLink>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator />
                        <BreadcrumbItem>
                            <BreadcrumbPage>{category.name}</BreadcrumbPage>
                        </BreadcrumbItem>
                    </BreadcrumbList>
                </Breadcrumb>

                {/* Page Header */}
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center gap-3">
                        {category.hasChildren ? (
                            <FolderOpen className="w-8 h-8 text-primary" />
                        ) : (
                            <Folder className="w-8 h-8 text-primary" />
                        )}
                        <div>
                            <h1 className="text-2xl font-bold tracking-tight">{category.name}</h1>
                            {category.fullPath && category.fullPath !== category.name && (
                                <p className="text-sm text-muted-foreground">{category.fullPath}</p>
                            )}
                        </div>
                    </div>
                    <div className="flex gap-2">
                        {can.edit && (
                            <Button asChild variant="outline">
                                <Link href={`/categories/${category.id}/edit`}>
                                    <Edit className="w-4 h-4 mr-2" />
                                    Edit
                                </Link>
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Info */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Basic Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <FileText className="w-5 h-5" />
                                    Basic Information
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-sm text-muted-foreground">Status</p>
                                        <Badge variant={category.isActive ? 'default' : 'secondary'}>
                                            {category.isActive ? 'Active' : 'Inactive'}
                                        </Badge>
                                    </div>
                                    <div>
                                        <p className="text-sm text-muted-foreground">Sort Order</p>
                                        <p className="font-medium">{category.sortOrder}</p>
                                    </div>
                                    {category.parentName && (
                                        <div className="col-span-2">
                                            <p className="text-sm text-muted-foreground">Parent Category</p>
                                            <Link 
                                                href={`/categories/${category.parentId}`}
                                                className="font-medium text-primary hover:underline"
                                            >
                                                {category.parentName}
                                            </Link>
                                        </div>
                                    )}
                                </div>
                                {category.description && (
                                    <div className="pt-4 border-t">
                                        <p className="text-sm text-muted-foreground">Description</p>
                                        <p className="mt-1">{category.description}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Products Info */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Package className="w-5 h-5" />
                                    Products
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-sm text-muted-foreground">Products in this category</p>
                                        <p className="text-3xl font-bold">{category.productsCount ?? 0}</p>
                                    </div>
                                    <Button asChild variant="outline">
                                        <Link href={`/products?category=${category.id}`}>
                                            View Products
                                        </Link>
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Child Categories */}
                        {childCategories.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <FolderOpen className="w-5 h-5" />
                                        Subcategories
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-2">
                                        {childCategories.map((child) => (
                                            <div 
                                                key={child.id} 
                                                className="flex items-center justify-between p-3 bg-muted rounded-lg"
                                            >
                                                <div className="flex items-center gap-2">
                                                    <Folder className="w-4 h-4 text-muted-foreground" />
                                                    <span className="font-medium">{child.name}</span>
                                                    <Badge variant={child.isActive ? 'default' : 'secondary'} className="text-xs">
                                                        {child.isActive ? 'Active' : 'Inactive'}
                                                    </Badge>
                                                </div>
                                                <Button asChild variant="ghost" size="sm">
                                                    <Link href={`/categories/${child.id}`}>
                                                        View
                                                    </Link>
                                                </Button>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Metadata */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Metadata</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Created</span>
                                    <span>{new Date(category.createdAt).toLocaleDateString()}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Updated</span>
                                    <span>{new Date(category.updatedAt).toLocaleDateString()}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Category ID</span>
                                    <span className="font-mono">#{category.id}</span>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Navigation */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Navigation</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <Button asChild variant="outline" className="w-full">
                                    <Link href="/categories">
                                        <ArrowLeft className="w-4 h-4 mr-2" />
                                        Back to Categories
                                    </Link>
                                </Button>
                                <Button asChild variant="outline" className="w-full">
                                    <Link href="/products">
                                        <Package className="w-4 h-4 mr-2" />
                                        View All Products
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </AppContentWrapper>
        </>
    );
}

CategoriesShow.layout = {
    breadcrumbs: [
        {
            title: 'Categories',
            href: '/categories',
        },
    ],
};
