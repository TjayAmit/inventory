import { Link } from '@inertiajs/react';
import { BookOpen, FolderGit2, LayoutGrid, Users, Package, Boxes, Building2, Truck, Warehouse } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as users } from '@/routes/users';
import { index as branches } from '@/routes/branches';
import { index as inventory } from '@/routes/inventory';
import { index as suppliers } from '@/routes/suppliers';
import { usePermission } from '@/hooks/use-permission';
import type { NavGroup, NavItem } from '@/types';

const navGroups: NavGroup[] = [
    {
        title: 'Overview',
        items: [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutGrid,
            },
        ],
    },
    {
        title: 'Management',
        items: [
            {
                title: 'Users',
                href: users(),
                icon: Users,
                permissions: ['users.view'],
                roles: ['admin'],
            },
            {
                title: 'Branches',
                href: branches(),
                icon: Building2,
                permissions: ['branches.view'],
                roles: ['admin'],
            },
            {
                title: 'Inventory',
                href: inventory(),
                icon: Boxes,
                permissions: ['inventory.view'],
                roles: ['admin'],
            },
            {
                title: 'Suppliers',
                href: suppliers(),
                icon: Truck,
                permissions: ['suppliers.view'],
                roles: ['admin'],
            },
        ],
    },
]

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { canAccess, hasRole } = usePermission();

    const filteredGroups = navGroups
        .filter((group) => !group.roles || hasRole(group.roles))
        .map((group) => ({
            ...group,
            items: group.items.filter(canAccess),
        }))
        .filter((group) => group.items.length > 0);

    return (
        <Sidebar collapsible="icon" variant="sidebar">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain groups={filteredGroups} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
