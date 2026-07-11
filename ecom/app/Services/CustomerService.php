<?php

namespace App\Services;

use App\Repositories\Interfaces\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function __construct(
        protected CustomerRepositoryInterface $customerRepository,
    ) {}

    public function getById(int $id)
    {
        return $this->customerRepository->findById($id);
    }

    public function getByUserId(int $userId)
    {
        return $this->customerRepository->findByUserId($userId);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->customerRepository->paginate($filters, $perPage);
    }

    public function getPurchaseHistory(int $customerId): LengthAwarePaginator
    {
        return $this->customerRepository->getPurchaseHistory($customerId);
    }

    public function delete(int $id): bool
    {
        return $this->customerRepository->delete($id);
    }

    public function update(int $id, array $data)
    {
        return $this->customerRepository->update($id, $data);
    }
}
