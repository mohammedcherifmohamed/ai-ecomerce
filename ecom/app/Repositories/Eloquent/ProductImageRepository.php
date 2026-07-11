<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductImage;
use App\Repositories\Interfaces\ProductImageRepositoryInterface;

class ProductImageRepository implements ProductImageRepositoryInterface
{
    public function __construct(
        protected ProductImage $model,
    ) {}

    public function findById(int $id): ProductImage
    {
        return $this->model->with('product')->findOrFail($id);
    }

    public function getByProductId(int $productId)
    {
        return $this->model->where('product_id', $productId)
            ->orderBy('sort_order')
            ->get();
    }

    public function create(array $data): ProductImage
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): ProductImage
    {
        $image = $this->findById($id);
        $image->update($data);

        return $image->fresh();
    }

    public function delete(int $id): bool
    {
        $image = $this->findById($id);

        return $image->delete();
    }

    public function setPrimary(int $productId, int $imageId): bool
    {
        $this->model->where('product_id', $productId)->update(['is_primary' => false]);
        $image = $this->model->findOrFail($imageId);

        return $image->update(['is_primary' => true]);
    }
}
