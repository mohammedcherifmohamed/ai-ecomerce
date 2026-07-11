<?php

namespace App\Repositories\Interfaces;

interface DocumentRepositoryInterface
{
    public function findById(int $id);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id): bool;

    public function paginate(array $filters = [], int $perPage = 15);

    public function getByUploader(int $userId, int $perPage = 15);

    public function count(): int;
}
