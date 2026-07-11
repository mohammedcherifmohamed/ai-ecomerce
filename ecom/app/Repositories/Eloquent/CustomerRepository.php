<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function __construct(
        protected Customer $model,
    ) {}

    public function findById(int $id): Customer
    {
        return $this->model->with('user')->findOrFail($id);
    }

    public function findByUserId(int $userId): ?Customer
    {
        return $this->model->with('user')->where('user_id', $userId)->first();
    }

    public function create(array $data): Customer
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Customer
    {
        $customer = $this->findById($id);
        $customer->update($data);

        return $customer->fresh();
    }

    public function delete(int $id): bool
    {
        $customer = $this->findById($id);

        return $customer->delete();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with('user');

        if (isset($filters['search'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getPurchaseHistory(int $customerId): LengthAwarePaginator
    {
        return $this->model->findOrFail($customerId)
            ->orders()
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }
}
