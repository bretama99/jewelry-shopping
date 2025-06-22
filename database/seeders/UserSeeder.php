<?php
// File: database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $adminRole = Role::where('slug', 'admin')->first();
        $managerRole = Role::where('slug', 'manager')->first();
        $salesRepRole = Role::where('slug', 'sales-rep')->first();
        $customerRole = Role::where('slug', 'customer')->first();

        $users = [
            [
                'first_name' => 'Brhane',
                'last_name' => 'Gidey',
                'email' => 'brhane@brhane.com',
                'password' => Hash::make('12345678'),
                'phone' => '+1234567890',
                'status' => 'active',
                'is_admin' => true,
                'role_id' => $superAdminRole?->id,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'email' => 'admin@jewelrystore.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567891',
                'status' => 'active',
                'is_admin' => true,
                'role_id' => $adminRole?->id,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Store',
                'last_name' => 'Manager',
                'email' => 'manager@jewelrystore.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567892',
                'status' => 'active',
                'is_admin' => false,
                'role_id' => $managerRole?->id,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Sales',
                'last_name' => 'Representative',
                'email' => 'sales@jewelrystore.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567893',
                'status' => 'active',
                'is_admin' => false,
                'role_id' => $salesRepRole?->id,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Customer',
                'email' => 'customer@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567894',
                'status' => 'active',
                'is_admin' => false,
                'role_id' => $customerRole?->id,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567895',
                'status' => 'active',
                'is_admin' => false,
                'role_id' => $customerRole?->id,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567896',
                'status' => 'inactive',
                'is_admin' => false,
                'role_id' => $customerRole?->id,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Demo users created successfully.');
    }
}
