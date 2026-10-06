<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name'        => 'Super Admin',
                'name_bn'     => 'সুপার অ্যাডমিন',
                'permissions' => ['*'],
                'is_system'   => true,
            ]
        );

        Role::updateOrCreate(
            ['slug' => 'manager'],
            [
                'name'        => 'Area Manager',
                'name_bn'     => 'এরিয়া ম্যানেজার',
                'permissions' => [
                    'dashboard.view',
                    'users.view',
                    'inventory.view',
                    'inventory.manage',
                    'partners.view',
                    'partners.manage',
                    'stock.view',
                    'stock.adjust',
                    'sales.view',
                    'sales.primary',
                    'sales.secondary',
                    'sr.tracking.view',
                    'accounting.view',
                    'accounting.manage',
                    'returns.view',
                    'returns.manage',
                    'kpi.view',
                    'kpi.manage',
                    'settings.manage',
                ],
                'is_system' => true,
            ]
        );

        Role::updateOrCreate(
            ['slug' => 'sr'],
            [
                'name'        => 'Sales Representative',
                'name_bn'     => 'বিক্রয় প্রতিনিধি (এসআর)',
                'permissions' => [
                    'dashboard.view',
                    'inventory.view',
                    'partners.view',
                    'sales.view',
                    'sales.secondary',
                    'sr.attendance',
                    'kpi.view',
                ],
                'is_system' => true,
            ]
        );

        Role::updateOrCreate(
            ['slug' => 'dealer'],
            [
                'name'        => 'Dealer',
                'name_bn'     => 'ডিলার',
                'permissions' => [
                    'dashboard.view',
                    'sales.view',
                    'sales.primary',
                ],
                'is_system' => true,
            ]
        );

        Role::updateOrCreate(
            ['slug' => 'retailer'],
            [
                'name'        => 'Retailer',
                'name_bn'     => 'রিটেইলার',
                'permissions' => [
                    'dashboard.view',
                    'sales.view',
                ],
                'is_system' => true,
            ]
        );

        // Default super admin login — CHANGE THIS PASSWORD IMMEDIATELY
        // after first login in production.
        User::updateOrCreate(
            ['email' => 'admin@ridneeltrade.com'],
            [
                'name'              => 'System Admin',
                'password'          => Hash::make('ChangeMe@123'),
                'role_id'           => $admin->id,
                'status'            => 'active',
                'preferred_locale'  => 'bn',
                'email_verified_at' => now(),
            ]
        );
    }
}
