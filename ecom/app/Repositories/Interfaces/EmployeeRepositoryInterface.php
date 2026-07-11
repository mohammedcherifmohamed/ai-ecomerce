<?php

namespace App\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EmployeeRepositoryInterface
{
    public function findById(int $id);

    public function findByUserId(int $userId);

    public function findByEmployeeId(string $employeeId);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id): bool;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function generateEmployeeId(): string;
}
