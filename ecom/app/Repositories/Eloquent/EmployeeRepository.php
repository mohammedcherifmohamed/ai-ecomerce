<?php

namespace App\Repositories\Eloquent;

use App\Models\Employee;
use App\Repositories\Interfaces\EmployeeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function __construct(
        protected Employee $model,
    ) {}

    public function findById(int $id): Employee
    {
        return $this->model->with('user')->findOrFail($id);
    }

    public function findByUserId(int $userId): ?Employee
    {
        return $this->model->with('user')->where('user_id', $userId)->first();
    }

    public function findByEmployeeId(string $employeeId): ?Employee
    {
        return $this->model->with('user')->where('employee_id', $employeeId)->first();
    }

    public function create(array $data): Employee
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Employee
    {
        $employee = $this->findById($id);
        $employee->update($data);

        return $employee->fresh();
    }

    public function delete(int $id): bool
    {
        $employee = $this->findById($id);

        return $employee->delete();
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

        if (isset($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function generateEmployeeId(): string
    {
        $lastEmployee = $this->model->latest('id')->first();
        $nextNumber = $lastEmployee ? (int) substr($lastEmployee->employee_id, 4) + 1 : 1;

        return 'EMP-'.str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
