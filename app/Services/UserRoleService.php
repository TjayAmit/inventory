<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserRoleService
{
    /**
     * Validate role assignment based on business rules.
     */
    public function validateRoleAssignment(array $roles, ?User $currentUser, ?User $targetUser = null): void
    {
        // Rule 1: At least one role must be assigned
        if (empty($roles)) {
            throw new \InvalidArgumentException('At least one role must be assigned.');
        }

        // Rule 2: All roles must exist
        $validRoles = ['admin', 'store_manager', 'cashier', 'warehouse_staff'];
        $invalidRoles = array_diff($roles, $validRoles);
        
        if (!empty($invalidRoles)) {
            throw new \InvalidArgumentException('Invalid role(s): ' . implode(', ', $invalidRoles));
        }

        // Rule 3: Non-admin users cannot assign admin role
        if (in_array('admin', $roles) && !$currentUser?->hasRole('admin')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Only administrators can assign admin role.');
        }

        // Rule 4: Store managers can only manage non-admin users
        if ($currentUser?->hasRole('store_manager') && in_array('admin', $roles)) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Store managers cannot assign admin role.');
        }

        // Rule 5: Cashiers and warehouse staff cannot manage roles
        if ($currentUser?->hasAnyRole(['cashier', 'warehouse_staff'])) {
            throw new \Illuminate\Auth\Access\AuthorizationException('You do not have permission to manage user roles.');
        }

        // Rule 6: Users cannot remove their own admin role
        if ($targetUser && $targetUser->id === $currentUser?->id && $targetUser->hasRole('admin') && !in_array('admin', $roles)) {
            throw new \Illuminate\Auth\Access\AuthorizationException('You cannot remove your own admin role.');
        }

        // Log role assignment validation
        Log::info('Role assignment validated', [
            'roles' => $roles,
            'current_user_id' => $currentUser?->id,
            'target_user_id' => $targetUser?->id,
        ]);
    }

    /**
     * Check if user can manage other users.
     */
    public function canManageUsers(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->hasAnyRole(['admin', 'store_manager']);
    }

    /**
     * Check if user can manage admin users.
     */
    public function canManageAdminUsers(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->hasRole('admin');
    }

    /**
     * Check if user can create users.
     */
    public function canCreateUsers(?User $user): bool
    {
        return $this->canManageUsers($user);
    }

    /**
     * Check if user can edit specific user.
     */
    public function canEditUser(?User $currentUser, User $targetUser): bool
    {
        if (!$currentUser) {
            return false;
        }

        // Admins can edit anyone
        if ($currentUser->hasRole('admin')) {
            return true;
        }

        // Store managers can edit non-admin users
        if ($currentUser->hasRole('store_manager') && !$targetUser->hasRole('admin')) {
            return true;
        }

        // Users can edit themselves (but not their roles)
        if ($currentUser->id === $targetUser->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can delete specific user.
     */
    public function canDeleteUser(?User $currentUser, User $targetUser): bool
    {
        if (!$currentUser) {
            return false;
        }

        // Cannot delete yourself
        if ($currentUser->id === $targetUser->id) {
            return false;
        }

        // Admins can delete anyone except themselves
        if ($currentUser->hasRole('admin')) {
            return true;
        }

        // Store managers can delete non-admin users
        if ($currentUser->hasRole('store_manager') && !$targetUser->hasRole('admin')) {
            return true;
        }

        return false;
    }

    /**
     * Get available roles that current user can assign.
     */
    public function getAvailableRolesForUser(?User $currentUser): array
    {
        if (!$currentUser) {
            return [];
        }

        $allRoles = [
            'admin' => 'Administrator',
            'store_manager' => 'Store Manager',
            'cashier' => 'Cashier',
            'warehouse_staff' => 'Warehouse Staff',
        ];

        // Admins can assign all roles
        if ($currentUser->hasRole('admin')) {
            return $allRoles;
        }

        // Store managers can assign non-admin roles
        if ($currentUser->hasRole('store_manager')) {
            return array_diff($allRoles, ['admin']);
        }

        // Other roles cannot assign any roles
        return [];
    }

    /**
     * Format role names for display.
     */
    public function formatRoleName(string $role): string
    {
        return match($role) {
            'admin' => 'Administrator',
            'store_manager' => 'Store Manager',
            'cashier' => 'Cashier',
            'warehouse_staff' => 'Warehouse Staff',
            default => ucfirst(str_replace('_', ' ', $role)),
        };
    }

    /**
     * Format multiple role names.
     */
    public function formatRoleNames(array $roles): array
    {
        return array_map([$this, 'formatRoleName'], $roles);
    }

    /**
     * Get role hierarchy level.
     */
    public function getRoleHierarchyLevel(string $role): int
    {
        return match($role) {
            'admin' => 4,
            'store_manager' => 3,
            'cashier' => 2,
            'warehouse_staff' => 1,
            default => 0,
        };
    }

    /**
     * Check if user has higher or equal role than target user.
     */
    public function hasEqualOrHigherRole(?User $currentUser, User $targetUser): bool
    {
        if (!$currentUser) {
            return false;
        }

        $currentUserLevel = max(array_map([$this, 'getRoleHierarchyLevel'], $currentUser->roles->pluck('name')->toArray()));
        $targetUserLevel = max(array_map([$this, 'getRoleHierarchyLevel'], $targetUser->roles->pluck('name')->toArray()));

        return $currentUserLevel >= $targetUserLevel;
    }
}
