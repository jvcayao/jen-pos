<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Store management
            'manage-stores',
            'view-all-stores',

            // Dashboard
            'view-dashboard',

            // Product management
            'manage-products',
            'view-products',

            // Category management
            'manage-categories',
            'view-categories',

            // Student management
            'manage-students',
            'view-students',

            // Order management
            'manage-orders',
            'process-orders',
            'void-orders',

            // User management
            'manage-users',
            'manage-store-users',

            // Reports
            'manage-reports',
            'view-reports',

            // Wallet transactions
            'process-wallet-transactions',

            // Discount codes
            'manage-discount-codes',
            'view-discount-codes',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions

        // Head Office Admin - Full access to everything
        $headOfficeAdmin = Role::firstOrCreate(['name' => 'head-office-admin', 'guard_name' => 'web']);
        $headOfficeAdmin->syncPermissions($permissions);

        // Store Admin - Manages their store's operations
        $storeAdmin = Role::firstOrCreate(['name' => 'store-admin', 'guard_name' => 'web']);
        $storeAdmin->syncPermissions([
            'view-dashboard',
            'manage-products',
            'view-products',
            'manage-categories',
            'view-categories',
            'manage-students',
            'view-students',
            'manage-orders',
            'process-orders',
            'void-orders',
            'manage-store-users',
            'manage-reports',
            'view-reports',
            'process-wallet-transactions',
            'manage-discount-codes',
            'view-discount-codes',
        ]);

        // Cashier - Limited to processing transactions
        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashier->syncPermissions([
            'view-products',
            'view-categories',
            'view-students',
            'process-orders',
            'view-reports',
            'process-wallet-transactions',
            'view-discount-codes',
        ]);
    }
}
