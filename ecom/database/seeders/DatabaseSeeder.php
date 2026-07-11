<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
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

        $laptops = Category::create(['name' => 'Laptops', 'slug' => 'laptops', 'description' => 'Laptop computers', 'parent_id' => $electronics->id]);
        $phones = Category::create(['name' => 'Phones', 'slug' => 'phones', 'description' => 'Mobile phones', 'parent_id' => $electronics->id]);

        Product::create([
            'category_id' => $laptops->id,
            'name' => 'MacBook Pro 16"',
            'slug' => 'macbook-pro-16',
            'description' => 'Apple MacBook Pro 16-inch with M3 Pro chip',
            'price' => 2499.00,
            'sku' => 'MBP-16-M3P',
            'stock_quantity' => 25,
            'is_featured' => true,
        ]);

        Product::create([
            'category_id' => $phones->id,
            'name' => 'iPhone 15 Pro',
            'slug' => 'iphone-15-pro',
            'description' => 'Apple iPhone 15 Pro with A17 Pro chip',
            'price' => 999.00,
            'sku' => 'IP15-PRO-256',
            'stock_quantity' => 50,
            'is_featured' => true,
        ]);

        Product::create([
            'category_id' => $clothing->id,
            'name' => 'Classic White T-Shirt',
            'slug' => 'classic-white-tshirt',
            'description' => '100% cotton classic white t-shirt',
            'price' => 29.99,
            'sku' => 'TSH-WHT-001',
            'stock_quantity' => 200,
            'low_stock_threshold' => 20,
        ]);

        Product::create([
            'category_id' => $home->id,
            'name' => 'Ceramic Plant Pot',
            'slug' => 'ceramic-plant-pot',
            'description' => 'Handmade ceramic plant pot, 8 inch diameter',
            'price' => 45.00,
            'sku' => 'POT-CER-008',
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
        ]);
    }
}
