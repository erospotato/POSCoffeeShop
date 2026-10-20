<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::firstOrCreate(['slug' => 'admin'], [
            'name' => 'Administrator',
            'description' => 'Full system access and configurations.',
        ]);

        $manager = Role::firstOrCreate(['slug' => 'manager'], [
            'name' => 'Manager',
            'description' => 'Operations, reports, inventory, suppliers, and approvals.',
        ]);

        $cashier = Role::firstOrCreate(['slug' => 'cashier'], [
            'name' => 'Cashier',
            'description' => 'POS orders, cashier shifts, customer transactions.',
        ]);

        $barista = Role::firstOrCreate(['slug' => 'barista'], [
            'name' => 'Barista',
            'description' => 'Kitchen Display System and beverage preparation.',
        ]);

        // Key permissions
        $permissions = [
            ['name' => 'View Admin Dashboard', 'slug' => 'view_admin_dashboard', 'module' => 'dashboard'],
            ['name' => 'View Manager Dashboard', 'slug' => 'view_manager_dashboard', 'module' => 'dashboard'],
            ['name' => 'Access POS', 'slug' => 'access_pos', 'module' => 'pos'],
            ['name' => 'Access KDS', 'slug' => 'access_kds', 'module' => 'kds'],
            ['name' => 'Manage Products', 'slug' => 'manage_products', 'module' => 'menu'],
            ['name' => 'Manage Inventory', 'slug' => 'manage_inventory', 'module' => 'inventory'],
            ['name' => 'Manage Suppliers', 'slug' => 'manage_suppliers', 'module' => 'suppliers'],
            ['name' => 'Manage Staff', 'slug' => 'manage_staff', 'module' => 'admin'],
            ['name' => 'View Financial Reports', 'slug' => 'view_reports', 'module' => 'reports'],
            ['name' => 'Issue Refunds', 'slug' => 'issue_refunds', 'module' => 'pos'],
            ['name' => 'Manage Settings', 'slug' => 'manage_settings', 'module' => 'admin'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // Attach permissions
        $admin->permissions()->sync(Permission::all());

        $managerPermissions = Permission::whereIn('slug', [
            'view_manager_dashboard', 'access_pos', 'access_kds', 'manage_products',
            'manage_inventory', 'manage_suppliers', 'view_reports', 'issue_refunds'
        ])->get();
        $manager->permissions()->sync($managerPermissions);

        $cashierPermissions = Permission::whereIn('slug', ['access_pos'])->get();
        $cashier->permissions()->sync($cashierPermissions);

        $baristaPermissions = Permission::whereIn('slug', ['access_kds'])->get();
        $barista->permissions()->sync($baristaPermissions);
    }
}
