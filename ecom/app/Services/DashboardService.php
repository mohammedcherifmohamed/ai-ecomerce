<?php

namespace App\Services;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;

class DashboardService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected ProductRepositoryInterface $productRepository,
        protected UserRepositoryInterface $userRepository,
    ) {}

    public function getStats(): array
    {
        return [
            'total_revenue' => $this->orderRepository->getTotalRevenue(),
            'total_orders' => $this->orderRepository->count(),
            'pending_orders' => $this->orderRepository->countByStatus('pending'),
            'processing_orders' => $this->orderRepository->countByStatus('processing'),
            'total_products' => $this->productRepository->count(),
            'low_stock_products' => $this->productRepository->getLowStockProducts()->count(),
            'total_customers' => $this->userRepository->countByRole('customer'),
            'total_employees' => $this->userRepository->countByRole('employee'),
            'best_selling_products' => $this->productRepository->getBestSellingProducts(5),
            'recent_orders' => $this->orderRepository->getRecentOrders(10),
            'monthly_orders' => $this->orderRepository->getMonthlyOrders(12),
        ];
    }
}
