<?php

namespace App\Services;

use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function getById(int $id)
    {
        return $this->categoryRepository->findById($id);
    }

    public function getBySlug(string $slug)
    {
        return $this->categoryRepository->findBySlug($slug);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->categoryRepository->paginate($filters, $perPage);
    }

    public function getActiveCategories(): Collection
    {
        return $this->categoryRepository->getActiveCategories();
    }

    public function getRootCategories(): Collection
    {
        return $this->categoryRepository->getRootCategories();
    }

    public function create(array $data)
    {
        return $this->categoryRepository->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->categoryRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->categoryRepository->delete($id);
    }

    public function count(): int
    {
        return $this->categoryRepository->count();
    }
}
