<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        protected User $model,
    ) {}

    public function findById(int $id): User
    {
        return $this->model->with(['customer', 'employee'])->findOrFail($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): User
    {
        $user = $this->findById($id);
        $user->update($data);

        return $user->fresh();
    }

    public function delete(int $id): bool
    {
        $user = $this->findById($id);

        return $user->delete();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['customer', 'employee']);

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getCustomers(): LengthAwarePaginator
    {
        return $this->model->where('role', 'customer')
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function countByRole(string $role): int
    {
        return $this->model->where('role', $role)->count();
    }
}
