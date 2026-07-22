<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected ProductRepositoryInterface $productRepository,
    ) {}

    public function getById(int $id)
    {
        return $this->orderRepository->findById($id);
    }

    public function getByOrderNumber(string $orderNumber)
    {
        return $this->orderRepository->findByOrderNumber($orderNumber);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderRepository->paginate($filters, $perPage);
    }

    public function getByCustomerId(int $customerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderRepository->getByCustomerId($customerId, $perPage);
    }

    public function create(array $data, array $items)
    {
        return DB::transaction(function () use ($data, $items) {
            $subtotal = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $product = $this->productRepository->findById($item['product_id']);

                if ($product->isOutOfStock()) {
                    throw new \InvalidArgumentException("Product '{$product->name}' is out of stock.");
                }

                if ($product->stock_quantity < $item['quantity']) {
                    throw new \InvalidArgumentException("Insufficient stock for product '{$product->name}'.");
                }

                $totalPrice = $product->price * $item['quantity'];
                $subtotal += $totalPrice;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $totalPrice,
                ];

                $this->productRepository->decrementStock($product->id, $item['quantity']);
            }

            $taxRate = 0.08;
            $taxAmount = round($subtotal * $taxRate, 2);
            $shippingAmount = $subtotal >= 100 ? 0 : 10.00;
            $totalAmount = $subtotal + $taxAmount + $shippingAmount;

            $order = $this->orderRepository->create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $data['customer_id'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'total_amount' => $totalAmount,
                'status' => OrderStatus::Pending,
                'shipping_address' => $data['shipping_address'] ?? null,
                'billing_address' => $data['billing_address'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => $data['payment_status'] ?? 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($orderItems as $orderItem) {
                $order->items()->create($orderItem);
            }

            $this->orderRepository->updateStatus(
                $order->id,
                OrderStatus::Pending->value,
                'Order placed',
                null
            );

            return $order->fresh(['customer.user', 'items.product', 'statusHistory']);
        });
    }

    public function updateStatus(int $orderId, string $status, ?string $notes = null, ?int $changedBy = null): Order
    {
        $order = $this->orderRepository->findById($orderId);
        $previousStatus = $order->status->value;

        $this->orderRepository->updateStatus($orderId, $status, $notes, $changedBy);

        if ($status === OrderStatus::Cancelled->value && $previousStatus !== OrderStatus::Cancelled->value) {
            $this->restoreStock($order);
        }

        $order = $this->orderRepository->findById($orderId);

        return $order;
    }

    public function refund(int $orderId, ?string $notes = null, ?int $changedBy = null): Order
    {
        $order = $this->updateStatus($orderId, OrderStatus::Refunded->value, $notes, $changedBy);
        $this->restoreStock($order);

        return $order;
    }

    public function cancel(int $orderId, ?string $notes = null, ?int $changedBy = null): Order
    {
        return $this->updateStatus($orderId, OrderStatus::Cancelled->value, $notes, $changedBy);
    }

    protected function restoreStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $this->productRepository->incrementStock($item->product_id, $item->quantity);
        }
    }

    public function count(): int
    {
        return $this->orderRepository->count();
    }

    public function getTotalRevenue(): float
    {
        return $this->orderRepository->getTotalRevenue();
    }

    public function getRecentOrders(int $limit = 10)
    {
        return $this->orderRepository->getRecentOrders($limit);
    }

    public function getMonthlyOrders(int $months = 12)
    {
        return $this->orderRepository->getMonthlyOrders($months);
    }

    public function getOrderStatusForAI($customerId,$orderId){
        // find order
        $order = $this->orderRepository->findCustomerOrder($customerId,$orderId);
        if(!$order){
            return [
                'success' => false ,
                'order' => [
                    'id' => $orderId ,
                    'status' => "not found" ,
                ],
        ];

        }

        return [
            'success' => true ,
            'order' => [
                'id' => $order->id ,
                'status' => $order->status ,
            ],
        ];

    }

    public function cancelOrderForAI($customerId, $orderId)
    {
        $order = $this->orderRepository->findCustomerOrder($customerId, $orderId);
        if (!$order) {
            return [
                'success' => false,
                'order' => ['id' => $orderId, 'status' => 'not found'],
            ];
        }

        $nonCancelable = [
            OrderStatus::Shipped->value,
            OrderStatus::Delivered->value,
            OrderStatus::Cancelled->value,
            OrderStatus::Refunded->value,
        ];

        $currentStatus = $order->status->value;
        if (in_array($currentStatus, $nonCancelable)) {
            return [
                'success' => false,
                'order' => ['id' => $order->id, 'status' => $currentStatus],
                'error' => "Cannot cancel an order with status '{$currentStatus}'.",
            ];
        }

        $this->cancel($orderId, 'Cancelled via AI assistant');
        $order->refresh();

        return [
            'success' => true,
            'order' => [
                'id' => $order->id,
                'status' => $order->status->value,
            ],
        ];
    }



}
