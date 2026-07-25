<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrderSeeder extends Seeder
{
    protected array $firstNames = ['James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda', 'David', 'Elizabeth',
        'William', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Jessica', 'Thomas', 'Sarah', 'Charles', 'Karen',
        'Christopher', 'Lisa', 'Daniel', 'Nancy', 'Matthew', 'Betty', 'Anthony', 'Margaret', 'Mark', 'Sandra'];

    protected array $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
        'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin'];

    protected array $cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'Austin'];

    protected array $states = ['NY', 'CA', 'IL', 'TX', 'AZ', 'PA', 'TX', 'CA', 'TX', 'TX'];

    protected array $streets = ['Oak St', 'Maple Ave', 'Pine Rd', 'Cedar Ln', 'Elm St', 'Birch Dr', 'Walnut Way', 'Cherry Ct', 'Willow Blvd', 'Main St'];

    protected array $paymentMethods = ['credit_card', 'paypal', 'bank_transfer', 'credit_card', 'paypal'];

    public function run(): void
    {
        $customers = $this->createCustomers();
        $productIds = Product::pluck('id')->toArray();
        $productData = Product::select('id', 'name', 'sku', 'price')->get()->keyBy('id');

        $statuses = OrderStatus::cases();
        $statusValues = array_map(fn($s) => $s->value, $statuses);

        for ($i = 0; $i < 200; $i++) {
            $customer = $customers[array_rand($customers)];
            $numItems = rand(1, 5);

            $itemProducts = [];
            $pickedIds = [];
            for ($j = 0; $j < $numItems; $j++) {
                $pid = $productIds[array_rand($productIds)];
                $pickedIds[] = $pid;
                $itemProducts[] = $productData[$pid];
            }

            $subtotal = 0;
            $items = [];
            foreach ($itemProducts as $prod) {
                $qty = rand(1, 3);
                $total = $prod->price * $qty;
                $subtotal += $total;
                $items[] = [
                    'product_id' => $prod->id,
                    'product_name' => $prod->name,
                    'product_sku' => $prod->sku,
                    'quantity' => $qty,
                    'unit_price' => $prod->price,
                    'total_price' => $total,
                ];
            }

            $tax = round($subtotal * 0.08, 2);
            $shipping = $subtotal >= 100 ? 0 : 10.00;
            $total = round($subtotal + $tax + $shipping, 2);

            $createdAt = now()->subDays(rand(1, 365))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            $status = $statusValues[array_rand($statusValues)];
            $paymentMethod = $this->paymentMethods[array_rand($this->paymentMethods)];

            $cityIdx = array_rand($this->cities);
            $address = rand(100, 9999) . ' ' . $this->streets[array_rand($this->streets)];

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'customer_id' => $customer->id,
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'shipping_amount' => $shipping,
                'discount_amount' => 0,
                'total_amount' => $total,
                'status' => $status,
                'shipping_address' => "{$address}, {$this->cities[$cityIdx]}, {$this->states[$cityIdx]} {$this->randomZip()}",
                'billing_address' => "{$address}, {$this->cities[$cityIdx]}, {$this->states[$cityIdx]} {$this->randomZip()}",
                'payment_method' => $paymentMethod,
                'payment_status' => $status === 'cancelled' ? 'refunded' : 'paid',
                'notes' => rand(0, 3) === 0 ? 'Customer requested gift wrapping.' : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_sku' => $item['product_sku'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $status,
                'notes' => 'Order placed',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            if (in_array($status, ['processing', 'shipped', 'delivered'])) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'processing',
                    'notes' => 'Payment confirmed, processing started',
                    'created_at' => (clone $createdAt)->addHours(rand(1, 12)),
                    'updated_at' => (clone $createdAt)->addHours(rand(1, 12)),
                ]);
            }

            if (in_array($status, ['shipped', 'delivered'])) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'shipped',
                    'notes' => 'Package shipped via USPS',
                    'created_at' => (clone $createdAt)->addDays(rand(1, 3)),
                    'updated_at' => (clone $createdAt)->addDays(rand(1, 3)),
                ]);
            }

            if ($status === 'delivered') {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'delivered',
                    'notes' => 'Package delivered successfully',
                    'created_at' => (clone $createdAt)->addDays(rand(4, 7)),
                    'updated_at' => (clone $createdAt)->addDays(rand(4, 7)),
                ]);
            }

            if ($status === 'cancelled') {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'cancelled',
                    'notes' => 'Cancelled by customer',
                    'created_at' => (clone $createdAt)->addHours(rand(2, 24)),
                    'updated_at' => (clone $createdAt)->addHours(rand(2, 24)),
                ]);
            }
        }
    }

    protected function createCustomers(): array
    {
        $customers = [];

        for ($i = 0; $i < 19; $i++) {
            $firstName = $this->firstNames[array_rand($this->firstNames)];
            $lastName = $this->lastNames[array_rand($this->lastNames)];
            $email = strtolower($firstName . '.' . $lastName . $i . '@example.com');

            $user = User::create([
                'name' => "{$firstName} {$lastName}",
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]);

            $cityIdx = array_rand($this->cities);
            $customer = Customer::create([
                'user_id' => $user->id,
                'phone' => $this->randomPhone(),
                'address' => rand(100, 9999) . ' ' . $this->streets[array_rand($this->streets)],
                'city' => $this->cities[$cityIdx],
                'state' => $this->states[$cityIdx],
                'zip_code' => $this->randomZip(),
                'country' => 'US',
            ]);

            $customers[] = $customer;
        }

        $existingCustomer = Customer::whereHas('user', fn($q) => $q->where('email', 'customer@example.com'))->first();
        if ($existingCustomer) {
            $customers[] = $existingCustomer;
        }

        return $customers;
    }

    protected function randomPhone(): string
    {
        return '555-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }

    protected function randomZip(): string
    {
        return str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
    }
}
