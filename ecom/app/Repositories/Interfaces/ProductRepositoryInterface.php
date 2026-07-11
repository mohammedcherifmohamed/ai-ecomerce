<?php

namespace App\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function findById(int $id);

    public function findBySlug(string $slug);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id): bool;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getActiveProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getFeaturedProducts(int $limit = 10): Collection;

    public function updateStock(int $productId, int $quantity): bool;

    public function decrementStock(int $productId, int $quantity): bool;

    public function incrementStock(int $productId, int $quantity): bool;

    public function getLowStockProducts(): Collection;

    public function count(): int;

    public function getBestSellingProducts(int $limit = 10): Collection;
}
