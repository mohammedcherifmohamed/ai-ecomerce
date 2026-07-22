<?php

namespace App\Repositories\Eloquent;

use App\Models\Inquiry;
use App\Repositories\Interfaces\InquiryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InquiryRepository implements InquiryRepositoryInterface
{
    public function __construct(
        protected Inquiry $model,
    ) {}

    public function create(string $inquiry, ?string $category = null): Inquiry
    {
        return $this->model->create([
            'inquiry' => $inquiry,
            'category' => $category,
        ]);
    }

    public function search(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query();

        if (!empty($filters['keyword'])) {
            $query->where('inquiry', 'like', "%{$filters['keyword']}%");
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $limit = $filters['limit'] ?? null;
        if ($limit) {
            return $query->orderBy('created_at', 'desc')->paginate($limit);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getCategoryCounts(?string $dateFrom = null, ?string $dateTo = null): Collection
    {
        $query = $this->model
            ->query()
            ->selectRaw('category, count(*) as total')
            ->groupBy('category');

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query->orderBy('total', 'desc')->get();
    }

    public function getDailyCounts(?string $dateFrom = null, ?string $dateTo = null): Collection
    {
        $query = $this->model
            ->query()
            ->selectRaw('date(created_at) as date, count(*) as total')
            ->groupByRaw('date(created_at)');

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query->orderBy('date', 'asc')->get();
    }
}
