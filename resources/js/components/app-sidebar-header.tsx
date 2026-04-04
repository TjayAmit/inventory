import { Bell } from 'lucide-react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    return (
        <header className="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/50 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>

            {/* Notification Bell */}
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="relative h-9 w-9"
                    >
                        <Bell className="h-5 w-5" />
                        {/* Notification Badge */}
                        <span className="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center">
                            <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-destructive opacity-75"></span>
                            <span className="relative inline-flex h-3.5 w-3.5 items-center justify-center rounded-full bg-destructive text-[10px] font-medium text-destructive-foreground">
                                3
                            </span>
                        </span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent className="w-80" align="end">
                    <DropdownMenuLabel className="font-semibold">
                        Notifications
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <div className="max-h-72 overflow-y-auto">
                        <DropdownMenuItem className="flex flex-col items-start gap-1 p-3 cursor-pointer">
                            <div className="flex w-full items-center justify-between">
                                <span className="font-medium">Low Stock Alert</span>
                                <Badge variant="destructive" className="text-[10px] px-1.5 py-0">New</Badge>
                            </div>
                            <p className="text-xs text-muted-foreground line-clamp-2">
                                Product "Wireless Mouse" is below reorder point (5 remaining)
                            </p>
                            <span className="text-[10px] text-muted-foreground">2 minutes ago</span>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem className="flex flex-col items-start gap-1 p-3 cursor-pointer">
                            <div className="flex w-full items-center justify-between">
                                <span className="font-medium">New Order</span>
                                <Badge variant="default" className="text-[10px] px-1.5 py-0">Order</Badge>
                            </div>
                            <p className="text-xs text-muted-foreground line-clamp-2">
                                Order #1234 received from Customer ABC Corp
                            </p>
                            <span className="text-[10px] text-muted-foreground">1 hour ago</span>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem className="flex flex-col items-start gap-1 p-3 cursor-pointer">
                            <div className="flex w-full items-center justify-between">
                                <span className="font-medium">System Update</span>
                                <Badge variant="secondary" className="text-[10px] px-1.5 py-0">Info</Badge>
                            </div>
                            <p className="text-xs text-muted-foreground line-clamp-2">
                                Inventory system maintenance scheduled for tonight
                            </p>
                            <span className="text-[10px] text-muted-foreground">3 hours ago</span>
                        </DropdownMenuItem>
                    </div>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem className="justify-center font-medium cursor-pointer">
                        View all notifications
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </header>
    );
}
