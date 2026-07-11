<?php

namespace App\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function findById(int $id);

    public function findBySlug(string $slug);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id): bool;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getActiveCategories(): Collection;

    public function getRootCategories(): Collection;

    public function count(): int;
}
