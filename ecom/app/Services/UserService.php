<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
    ) {}

    public function getById(int $id)
    {
        return $this->userRepository->findById($id);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($filters, $perPage);
    }

    public function getCustomers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->getCustomers();
    }

    public function update(int $id, array $data)
    {
        return $this->userRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function countByRole(string $role): int
    {
        return $this->userRepository->countByRole($role);
    }
}
