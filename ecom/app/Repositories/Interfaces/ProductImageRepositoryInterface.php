<?php

namespace App\Repositories\Interfaces;

interface ProductImageRepositoryInterface
{
    public function findById(int $id);

    public function getByProductId(int $productId);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id): bool;

    public function setPrimary(int $productId, int $imageId): bool;
}
