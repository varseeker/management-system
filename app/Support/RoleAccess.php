<?php

namespace App\Support;

class RoleAccess
{
    public static function isAdmin(string $role): bool
    {
        return $role === 'admin';
    }

    public static function isOwner(string $role): bool
    {
        return $role === 'owner';
    }

    public static function isStaff(string $role): bool
    {
        return $role === 'staff';
    }

    public static function canManageInventory(string $role): bool
    {
        return in_array($role, ['admin', 'owner'], true);
    }

    public static function canManageApprovals(string $role): bool
    {
        return in_array($role, ['admin', 'owner'], true);
    }

    public static function canManageUsers(string $role): bool
    {
        return $role === 'admin';
    }

    public static function canExport(string $role): bool
    {
        return in_array($role, ['admin', 'owner'], true);
    }

    public static function canViewAllBorrowings(string $role): bool
    {
        return in_array($role, ['admin', 'owner'], true);
    }

    public static function canProcessOrders(string $role): bool
    {
        return in_array($role, ['admin', 'owner', 'staff'], true);
    }

    public static function label(string $role): string
    {
        return config("inventory.roles.{$role}", ucfirst($role));
    }
}
