<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        protected Order $model,
        protected OrderStatusHistory $statusHistoryModel,
    ) {}

    public function findById(int $id): Order
    {
        return $this->model->with(['customer.user', 'items.product', 'statusHistory.changedByUser'])
            ->findOrFail($id);
    }

    public function findByOrderNumber(string $orderNumber): Order
    {
        return $this->model->with(['customer.user', 'items.product'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();
    }

    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Order
    {
        $order = $this->findById($id);
        $order->update($data);

        return $order->fresh(['customer.user', 'items.product']);
    }

    public function delete(int $id): bool
    {
        $order = $this->findById($id);

        return $order->delete();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['customer.user', 'items']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['search'])) {
            $query->where('order_number', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getByCustomerId(int $customerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['items.product'])
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function updateStatus(int $orderId, string $status, ?string $notes = null, ?int $changedBy = null): bool
    {
        $order = $this->model->findOrFail($orderId);
        $order->update(['status' => $status]);

        if ($status === 'shipped') {
            $order->update(['shipped_at' => now()]);
        }

        if ($status === 'delivered') {
            $order->update(['delivered_at' => now()]);
        }

        $this->statusHistoryModel->create([
            'order_id' => $orderId,
            'status' => $status,
            'notes' => $notes,
            'changed_by' => $changedBy,
        ]);

        return true;
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function countByStatus(string $status): int
    {
        return $this->model->where('status', $status)->count();
    }

    public function getTotalRevenue(): float
    {
        return (float) $this->model->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'refunded')
            ->sum('total_amount');
    }

    public function getRevenueByDateRange(string $startDate, string $endDate): float
    {
        return (float) $this->model->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'refunded')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');
    }

    public function getRecentOrders(int $limit = 10): Collection
    {
        return $this->model->with('customer.user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getMonthlyOrders(int $months = 12): Collection
    {
        return $this->model->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count, SUM(total_amount) as revenue')
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy(\DB::raw('YEAR(created_at)'), \DB::raw('MONTH(created_at)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }
}
