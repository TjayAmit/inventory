import { usePage } from '@inertiajs/react';

export function usePermissions() {
    const { props } = usePage();
    const auth = props.auth;
    const user = auth?.user;
    const permissions = auth?.permissions || [];


    console.log(permissions);
    // Check if user is authenticated
    const isAuthenticated = !!user;

    // Multi-layer permission caching for reliability
    const getPersistentPermissions = (): string[] => {
        // Priority 1: Current page permissions
        if (permissions.length > 0) {
            return permissions;
        }

        // Priority 2: Window cache (for navigation)
        if (typeof window !== 'undefined' && (window as any).__cachedPermissions?.length > 0) {
            return (window as any).__cachedPermissions;
        }

        // Priority 3: localStorage (for fresh page loads)
        if (typeof window !== 'undefined') {
            try {
                const stored = localStorage.getItem('user_permissions');
                if (stored) {
                    return JSON.parse(stored);
                }
            } catch (e) {
                console.warn('Failed to parse stored permissions:', e);
            }
        }

        return [];
    };

    // Update all cache layers when we have fresh permissions
    if (permissions.length > 0) {
        // Update window cache
        if (typeof window !== 'undefined') {
            (window as any).__cachedPermissions = [...permissions];
        }

        // Update localStorage
        if (typeof window !== 'undefined') {
            try {
                localStorage.setItem('user_permissions', JSON.stringify(permissions));
            } catch (e) {
                console.warn('Failed to store permissions:', e);
            }
        }
    }

    // Use the most reliable permissions available
    const effectivePermissions = getPersistentPermissions();

    const hasPermission = (permission: string | string[] | undefined): boolean => {
        // First check if user is authenticated
        if (!isAuthenticated) {
            return false;
        }

        // If no permission required, allow access
        if (!permission) {
            return true;
        }
        
        // Check if user has the required permission(s)
        if (Array.isArray(permission)) {
            return permission.some(p => effectivePermissions.includes(p));
        }
        
        return effectivePermissions.includes(permission);
    };

    const hasAnyPermission = (perms: string[]): boolean => {
        if (!isAuthenticated) return false;
        return perms.some(p => getPersistentPermissions().includes(p));
    };

    const hasAllPermissions = (perms: string[]): boolean => {
        if (!isAuthenticated) return false;
        return perms.every(p => getPersistentPermissions().includes(p));
    };

    // Check if user can access a specific sidebar item
    const canAccessSidebarItem = (item: { title?: string; permission?: string | string[] }): boolean => {
        // Must be authenticated
        if (!isAuthenticated) {
            return false;
        }

        // Check if item has permission requirements
        if (!item.permission) {
            return true; // No permission required
        }

        // Validate user has the required permission(s)
        return hasPermission(item.permission);
    };

    // Get user role information
    const getUserRoles = (): string[] => {
        if (!isAuthenticated || !user?.roles) {
            return [];
        }
        
        return Array.isArray(user.roles) 
            ? user.roles.map((role: any) => role.name || role)
            : [];
    };

    // Check if user has specific role
    const hasRole = (role: string | string[]): boolean => {
        if (!isAuthenticated) return false;
        
        const userRoles = getUserRoles();
        
        if (Array.isArray(role)) {
            return role.some(r => userRoles.includes(r));
        }
        
        return userRoles.includes(role);
    };

    return {
        user,
        permissions: effectivePermissions,
        isAuthenticated,
        hasPermission,
        hasAnyPermission,
        hasAllPermissions,
        canAccessSidebarItem,
        getUserRoles,
        hasRole,
    };
}
