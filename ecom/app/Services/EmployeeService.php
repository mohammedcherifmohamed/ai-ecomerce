<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Interfaces\EmployeeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    public function __construct(
        protected EmployeeRepositoryInterface $employeeRepository,
    ) {}

    public function getById(int $id)
    {
        return $this->employeeRepository->findById($id);
    }

    public function getByUserId(int $userId)
    {
        return $this->employeeRepository->findByUserId($userId);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->employeeRepository->paginate($filters, $perPage);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => UserRole::Employee,
            ]);

            $employeeId = $data['employee_id'] ?? $this->employeeRepository->generateEmployeeId();

            return $this->employeeRepository->create([
                'user_id' => $user->id,
                'employee_id' => $employeeId,
                'department' => $data['department'] ?? null,
                'position' => $data['position'] ?? null,
                'hire_date' => $data['hire_date'],
            ]);
        });
    }

    public function update(int $id, array $data)
    {
        $employee = $this->employeeRepository->findById($id);

        if (isset($data['name']) || isset($data['email'])) {
            $userData = array_filter(['name' => $data['name'] ?? null, 'email' => $data['email'] ?? null]);
            $employee->user->update($userData);
        }

        $employeeData = array_filter([
            'department' => $data['department'] ?? null,
            'position' => $data['position'] ?? null,
            'hire_date' => $data['hire_date'] ?? null,
        ]);

        return $this->employeeRepository->update($id, $employeeData);
    }

    public function delete(int $id): bool
    {
        $employee = $this->employeeRepository->findById($id);
        $employee->user->delete();

        return $this->employeeRepository->delete($id);
    }
}
