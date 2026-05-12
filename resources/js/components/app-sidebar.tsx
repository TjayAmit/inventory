import { Link } from '@inertiajs/react';
import { BookOpen, FolderGit2, LayoutGrid, Users, Package, Boxes, Building2, Truck, ShoppingCart, FileText, UserCog } from 'lucide-react';
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
import { index as products } from '@/routes/products';
import { index as suppliers } from '@/routes/suppliers';
import { index as salesOrders } from '@/routes/sales-orders';
import { index as invoices } from '@/routes/invoices';
import { index as personnel } from '@/routes/personnel';
import { usePermission } from '@/hooks/use-permission';
import type { NavGroup, NavItem } from '@/types';

const ALL_STAFF = ['admin', 'owner', 'store_manager', 'cashier', 'warehouse_staff'] as const;

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
        title: 'Administration',
        items: [
            {
                title: 'Users',
                href: users(),
                icon: Users,
                roles: ['admin'],
            },
            {
                title: 'Branches',
                href: branches(),
                icon: Building2,
                roles: ['admin', 'owner'],
            },
            {
                title: 'Personnel',
                href: personnel(),
                icon: UserCog,
                roles: ['admin', 'owner'],
            },
        ],
    },
    {
        title: 'Catalog',
        items: [
            {
                title: 'Products',
                href: products(),
                icon: Package,
                roles: [...ALL_STAFF],
            },
            {
                title: 'Suppliers',
                href: suppliers(),
                icon: Truck,
                roles: ['admin', 'owner', 'store_manager'],
            },
        ],
    },
    {
        title: 'Inventory',
        items: [
            {
                title: 'Inventory',
                href: inventory(),
                icon: Boxes,
                roles: ['admin', 'owner', 'store_manager', 'warehouse_staff'],
            },
        ],
    },
    {
        title: 'Sales',
        items: [
            {
                title: 'Sales Orders',
                href: salesOrders(),
                icon: ShoppingCart,
                roles: ['admin', 'owner', 'store_manager'],
            },
            {
                title: 'Invoice',
                href: invoices(),
                icon: FileText,
                roles: ['admin', 'owner', 'store_manager', 'cashier'],
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
