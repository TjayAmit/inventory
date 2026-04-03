import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { ArrowLeft, Edit, Mail } from 'lucide-react';

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
        <AppLayout>
            <Head title={`User: ${user.name}`} />

            <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div className="p-6 bg-white border-b border-gray-200">
                        <div className="flex justify-between items-center mb-6">
                            <h1 className="text-2xl font-semibold text-gray-900">User Details</h1>
                            <Link
                                href="/users"
                                className="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                            >
                                <ArrowLeft className="w-4 h-4 mr-2" />
                                Back to Users
                            </Link>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                <div className="space-y-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Name</label>
                                        <p className="mt-1 text-sm text-gray-900">{user.name}</p>
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Email</label>
                                        <p className="mt-1 text-sm text-gray-900">{user.email}</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Roles</h3>
                                <div className="flex flex-wrap gap-2">
                                    {user.roles.map((role) => (
                                        <span
                                            key={role.id}
                                            className="px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-800"
                                        >
                                            {role.name.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                        </span>
                                    ))}
                                </div>
                            </div>

                            <div>
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Permissions</h3>
                                <div className="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    {user.permissions.map((permission) => (
                                        <span
                                            key={permission.id}
                                            className="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800"
                                        >
                                            {permission.name.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                        </span>
                                    ))}
                                </div>
                            </div>

                            <div className="md:col-span-2">
                                <div className="flex justify-end space-x-3">
                                    <Link
                                        href={`/users/${user.id}/edit`}
                                        className="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        <Edit className="w-4 h-4 mr-2" />
                                        Edit User
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
