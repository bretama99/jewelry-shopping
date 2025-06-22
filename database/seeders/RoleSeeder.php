<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Super administrator with full system access',
                'permissions' => [
                    'users.view', 'users.create', 'users.edit', 'users.delete', 'users.manage',
                    'roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'roles.manage',
                    'categories.view', 'categories.create', 'categories.edit', 'categories.delete', 'categories.manage',
                    'products.view', 'products.create', 'products.edit', 'products.delete', 'products.manage',
                    'orders.view', 'orders.create', 'orders.edit', 'orders.delete', 'orders.manage',
                    'admin.access', 'admin.dashboard', 'admin.settings', 'admin.reports',
                    'system.cache', 'system.maintenance',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrator with standard management access',
                'permissions' => [
                    'users.view', 'users.edit',
                    'categories.view', 'categories.create', 'categories.edit', 'categories.delete', 'categories.manage',
                    'products.view', 'products.create', 'products.edit', 'products.delete', 'products.manage',
                    'orders.view', 'orders.create', 'orders.edit', 'orders.delete', 'orders.manage',
                    'admin.access', 'admin.dashboard', 'admin.reports',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Regular customer with basic access',
                'permissions' => [
                    'products.view', 'orders.view', 'orders.create',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        $this->command->info('Roles seeded successfully!');
    }
}