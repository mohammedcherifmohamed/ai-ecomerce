<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Administrator,
        ]);

        $employee = User::create([
            'name' => 'Employee',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Employee,
        ]);

        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Customer,
        ]);

        $admin->employee()->create([
            'employee_id' => 'EMP-00001',
            'department' => 'Management',
            'position' => 'Administrator',
            'hire_date' => now()->subYear(),
        ]);

        $employee->employee()->create([
            'employee_id' => 'EMP-00002',
            'department' => 'Sales',
            'position' => 'Sales Representative',
            'hire_date' => now()->subMonths(6),
        ]);

        $customer->customer()->create([
            'phone' => '555-0100',
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10001',
            'country' => 'US',
        ]);

        $electronics = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic devices']);
        $clothing = Category::create(['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Apparel and accessories']);
        $home = Category::create(['name' => 'Home & Garden', 'slug' => 'home-garden', 'description' => 'Home and garden products']);

        $this->call([
            ProductSeeder::class,
            OrderSeeder::class,
            InquirySeeder::class,
            ReadyEmailSeeder::class,
        ]);
    }
}
