<?php

namespace App\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(int $id);

    public function findByEmail(string $email);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id): bool;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getCustomers(): LengthAwarePaginator;

    public function countByRole(string $role): int;
}
