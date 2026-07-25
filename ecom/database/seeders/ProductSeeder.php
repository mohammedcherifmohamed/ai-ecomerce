<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::where('slug', 'electronics')->first();
        $clothing = Category::where('slug', 'clothing')->first();
        $home = Category::where('slug', 'home-garden')->first();

        $laptops = Category::firstOrCreate(['slug' => 'laptops'], ['name' => 'Laptops', 'description' => 'Laptop computers', 'parent_id' => $electronics->id, 'sort_order' => 1]);
        $phones = Category::firstOrCreate(['slug' => 'phones'], ['name' => 'Phones', 'description' => 'Mobile phones', 'parent_id' => $electronics->id, 'sort_order' => 2]);
        $accessories = Category::firstOrCreate(['slug' => 'accessories'], ['name' => 'Accessories', 'description' => 'Electronic accessories', 'parent_id' => $electronics->id, 'sort_order' => 3]);
        $audio = Category::firstOrCreate(['slug' => 'audio'], ['name' => 'Audio', 'description' => 'Audio equipment', 'parent_id' => $electronics->id, 'sort_order' => 4]);

        $men = Category::firstOrCreate(['slug' => 'men'], ['name' => 'Men', 'description' => 'Men\'s clothing', 'parent_id' => $clothing->id, 'sort_order' => 1]);
        $women = Category::firstOrCreate(['slug' => 'women'], ['name' => 'Women', 'description' => 'Women\'s clothing', 'parent_id' => $clothing->id, 'sort_order' => 2]);

        $kitchen = Category::firstOrCreate(['slug' => 'kitchen'], ['name' => 'Kitchen', 'description' => 'Kitchen and dining', 'parent_id' => $home->id, 'sort_order' => 1]);
        $decor = Category::firstOrCreate(['slug' => 'decor'], ['name' => 'Decor', 'description' => 'Home decor', 'parent_id' => $home->id, 'sort_order' => 2]);

        $products = [
            // Laptops (6)
            ['category' => $laptops, 'name' => 'MacBook Pro 16" M3 Pro', 'price' => 2499.00, 'sku' => 'MBP-16-M3P', 'stock' => 15, 'featured' => true],
            ['category' => $laptops, 'name' => 'MacBook Air 15" M3', 'price' => 1299.00, 'sku' => 'MBA-15-M3', 'stock' => 30, 'featured' => true],
            ['category' => $laptops, 'name' => 'Dell XPS 15', 'price' => 1899.00, 'sku' => 'DELL-XPS-15', 'stock' => 20, 'featured' => false],
            ['category' => $laptops, 'name' => 'Lenovo ThinkPad X1 Carbon', 'price' => 2149.00, 'sku' => 'LEN-TP-X1C', 'stock' => 12, 'featured' => false],
            ['category' => $laptops, 'name' => 'Microsoft Surface Laptop 5', 'price' => 1599.00, 'sku' => 'MS-SL5', 'stock' => 18, 'featured' => false],
            ['category' => $laptops, 'name' => 'ASUS ROG Zephyrus G14', 'price' => 1799.00, 'sku' => 'ASUS-ROG-G14', 'stock' => 8, 'featured' => false],

            // Phones (6)
            ['category' => $phones, 'name' => 'iPhone 15 Pro Max', 'price' => 1199.00, 'sku' => 'IP15-PM-256', 'stock' => 40, 'featured' => true],
            ['category' => $phones, 'name' => 'iPhone 15', 'price' => 799.00, 'sku' => 'IP15-128', 'stock' => 55, 'featured' => false],
            ['category' => $phones, 'name' => 'Samsung Galaxy S24 Ultra', 'price' => 1299.00, 'sku' => 'S24-ULTRA', 'stock' => 25, 'featured' => true],
            ['category' => $phones, 'name' => 'Google Pixel 8 Pro', 'price' => 999.00, 'sku' => 'PIXEL-8P', 'stock' => 22, 'featured' => false],
            ['category' => $phones, 'name' => 'OnePlus 12', 'price' => 799.00, 'sku' => 'OP12', 'stock' => 30, 'featured' => false],
            ['category' => $phones, 'name' => 'Nothing Phone (2)', 'price' => 599.00, 'sku' => 'NOTHING-2', 'stock' => 20, 'featured' => false],

            // Accessories (8)
            ['category' => $accessories, 'name' => 'Apple AirPods Pro 2', 'price' => 249.00, 'sku' => 'AAP2-USB', 'stock' => 80, 'featured' => true],
            ['category' => $accessories, 'name' => 'Samsung Galaxy Buds2 Pro', 'price' => 189.00, 'sku' => 'SGB2P', 'stock' => 60, 'featured' => false],
            ['category' => $accessories, 'name' => 'Logitech MX Master 3S Mouse', 'price' => 99.99, 'sku' => 'LOG-MX3S', 'stock' => 45, 'featured' => false],
            ['category' => $accessories, 'name' => 'Apple Magic Keyboard', 'price' => 149.00, 'sku' => 'APL-MK', 'stock' => 35, 'featured' => false],
            ['category' => $accessories, 'name' => 'Anker USB-C Hub 7-in-1', 'price' => 34.99, 'sku' => 'ANK-7IN1', 'stock' => 100, 'featured' => false],
            ['category' => $accessories, 'name' => 'Belkin Wireless Charger', 'price' => 29.99, 'sku' => 'BELK-WC', 'stock' => 120, 'featured' => false],
            ['category' => $accessories, 'name' => 'Spigen iPhone 15 Pro Case', 'price' => 19.99, 'sku' => 'SP-IP15C', 'stock' => 150, 'featured' => false],
            ['category' => $accessories, 'name' => 'Tempered Glass Screen Protector', 'price' => 12.99, 'sku' => 'TG-SP-U', 'stock' => 200, 'featured' => false],

            // Audio (5)
            ['category' => $audio, 'name' => 'Sony WH-1000XM5 Headphones', 'price' => 349.00, 'sku' => 'SONY-XM5', 'stock' => 25, 'featured' => true],
            ['category' => $audio, 'name' => 'JBL Flip 6 Speaker', 'price' => 129.00, 'sku' => 'JBL-FLIP6', 'stock' => 40, 'featured' => false],
            ['category' => $audio, 'name' => 'Marshall Stanmore II Speaker', 'price' => 299.00, 'sku' => 'MRS-ST2', 'stock' => 15, 'featured' => false],
            ['category' => $audio, 'name' => 'Bose QuietComfort Earbuds II', 'price' => 279.00, 'sku' => 'BOSE-QCE2', 'stock' => 20, 'featured' => false],
            ['category' => $audio, 'name' => 'Audio-Technica ATH-M50x', 'price' => 169.00, 'sku' => 'AT-ATHM50X', 'stock' => 30, 'featured' => false],

            // Men (8)
            ['category' => $men, 'name' => 'Classic Fit Oxford Shirt', 'price' => 79.99, 'sku' => 'M-OXFORD-BW', 'stock' => 100, 'featured' => false],
            ['category' => $men, 'name' => 'Slim Fit Chinos', 'price' => 69.99, 'sku' => 'M-CHINO-KH', 'stock' => 80, 'featured' => false],
            ['category' => $men, 'name' => 'Wool Blend Blazer', 'price' => 199.99, 'sku' => 'M-BLAZER-NAV', 'stock' => 25, 'featured' => true],
            ['category' => $men, 'name' => 'Leather Chelsea Boots', 'price' => 159.99, 'sku' => 'M-BOOTS-BRN', 'stock' => 35, 'featured' => true],
            ['category' => $men, 'name' => 'Cashmere V-Neck Sweater', 'price' => 129.99, 'sku' => 'M-SWTR-GRY', 'stock' => 40, 'featured' => false],
            ['category' => $men, 'name' => 'Denim Jacket', 'price' => 89.99, 'sku' => 'M-DNJKT-BLU', 'stock' => 50, 'featured' => false],
            ['category' => $men, 'name' => 'Cotton Polo Shirt', 'price' => 59.99, 'sku' => 'M-POLO-NAV', 'stock' => 120, 'featured' => false],
            ['category' => $men, 'name' => 'Running Sneakers', 'price' => 119.99, 'sku' => 'M-SNKR-BLK', 'stock' => 60, 'featured' => false],

            // Women (8)
            ['category' => $women, 'name' => 'Floral Summer Dress', 'price' => 89.99, 'sku' => 'W-DRESS-FL', 'stock' => 45, 'featured' => true],
            ['category' => $women, 'name' => 'Tailored Blazer', 'price' => 179.99, 'sku' => 'W-BLAZER-BLK', 'stock' => 30, 'featured' => false],
            ['category' => $women, 'name' => 'Silk Blouse', 'price' => 99.99, 'sku' => 'W-BLOUSE-CRM', 'stock' => 55, 'featured' => false],
            ['category' => $women, 'name' => 'High-Waist Jeans', 'price' => 79.99, 'sku' => 'W-JEANS-BLU', 'stock' => 65, 'featured' => false],
            ['category' => $women, 'name' => 'Leather Handbag', 'price' => 249.99, 'sku' => 'W-BAG-TAN', 'stock' => 20, 'featured' => true],
            ['category' => $women, 'name' => 'Wool Coat', 'price' => 299.99, 'sku' => 'W-COAT-CML', 'stock' => 15, 'featured' => true],
            ['category' => $women, 'name' => 'Ballet Flats', 'price' => 69.99, 'sku' => 'W-FLTS-BLK', 'stock' => 70, 'featured' => false],
            ['category' => $women, 'name' => 'Cashmere Scarf', 'price' => 49.99, 'sku' => 'W-SCARF-BRG', 'stock' => 90, 'featured' => false],

            // Kitchen (5)
            ['category' => $kitchen, 'name' => 'Stainless Steel Cookware Set', 'price' => 299.99, 'sku' => 'H-CKW-SS10', 'stock' => 12, 'featured' => true],
            ['category' => $kitchen, 'name' => 'Chefs Knife 8 inch', 'price' => 79.99, 'sku' => 'H-KNIFE-PRO', 'stock' => 40, 'featured' => false],
            ['category' => $kitchen, 'name' => 'Cast Iron Dutch Oven', 'price' => 149.99, 'sku' => 'H-DUTCH-CI', 'stock' => 18, 'featured' => false],
            ['category' => $kitchen, 'name' => 'Bamboo Cutting Board Set', 'price' => 44.99, 'sku' => 'H-BOARD-BAM', 'stock' => 55, 'featured' => false],
            ['category' => $kitchen, 'name' => 'Electric Kettle', 'price' => 39.99, 'sku' => 'H-KETTLE-SS', 'stock' => 60, 'featured' => false],

            // Decor (4)
            ['category' => $decor, 'name' => 'Ceramic Vase Set', 'price' => 54.99, 'sku' => 'H-VASE-CER', 'stock' => 25, 'featured' => false],
            ['category' => $decor, 'name' => 'Scented Candle Collection', 'price' => 39.99, 'sku' => 'H-CANDLE-3', 'stock' => 45, 'featured' => false],
            ['category' => $decor, 'name' => 'Wall Art Canvas Print', 'price' => 79.99, 'sku' => 'H-ART-CNV', 'stock' => 20, 'featured' => true],
            ['category' => $decor, 'name' => 'Throw Pillow Set', 'price' => 49.99, 'sku' => 'H-PILLOW-S', 'stock' => 35, 'featured' => false],
        ];

        foreach ($products as $data) {
            Product::create([
                'category_id' => $data['category']->id,
                'name' => $data['name'],
                'slug' => str()->slug($data['name']),
                'description' => "Premium {$data['name']} — high quality product.",
                'price' => $data['price'],
                'sku' => $data['sku'],
                'stock_quantity' => $data['stock'],
                'is_featured' => $data['featured'],
            ]);
        }
    }
}
