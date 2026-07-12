<?php

namespace App\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Order;

interface OrderRepositoryInterface
{
    public function findById(int $id);

    public function findByOrderNumber(string $orderNumber);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id): bool;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getByCustomerId(int $customerId, int $perPage = 15): LengthAwarePaginator;

    public function updateStatus(int $orderId, string $status, ?string $notes = null, ?int $changedBy = null): bool;

    public function count(): int;

    public function countByStatus(string $status): int;

    public function getTotalRevenue(): float;

    public function getRevenueByDateRange(string $startDate, string $endDate): float;

    public function getRecentOrders(int $limit = 10): Collection;

    public function getMonthlyOrders(int $months = 12): Collection;

    public function findCustomerOrder(int $customerId , int $orderId): ?Order ;
}
