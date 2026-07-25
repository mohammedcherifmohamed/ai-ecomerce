<?php

namespace App\Repositories\Interfaces;

use App\Models\ReadyEmail;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReadyEmailRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ReadyEmail;

    public function update(int $id, array $data): ReadyEmail;

    public function markAsSent(int $id): bool;

    public function markBulkAsSent(array $ids): int;
}