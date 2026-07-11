<?php

namespace App\Services;

use App\Repositories\Interfaces\ProductImageRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected ProductImageRepositoryInterface $imageRepository,
    ) {}

    public function getById(int $id)
    {
        return $this->productRepository->findById($id);
    }

    public function getBySlug(string $slug)
    {
        return $this->productRepository->findBySlug($slug);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->paginate($filters, $perPage);
    }

    public function getActiveProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getActiveProducts($filters, $perPage);
    }

    public function getFeaturedProducts(int $limit = 10): Collection
    {
        return $this->productRepository->getFeaturedProducts($limit);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] = Str::slug($data['name']);

            $product = $this->productRepository->create($data);

            if (isset($data['images'])) {
                $this->uploadImages($product->id, $data['images']);
            }

            return $product->fresh(['category', 'images']);
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            if (isset($data['name']) && ! isset($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $product = $this->productRepository->update($id, $data);

            if (isset($data['images'])) {
                $this->uploadImages($id, $data['images']);
            }

            return $product->fresh(['category', 'images']);
        });
    }

    public function delete(int $id): bool
    {
        $product = $this->productRepository->findById($id);

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        return $this->productRepository->delete($id);
    }

    public function updateStock(int $productId, int $quantity): bool
    {
        return $this->productRepository->updateStock($productId, $quantity);
    }

    public function getLowStockProducts(): Collection
    {
        return $this->productRepository->getLowStockProducts();
    }

    public function getBestSellingProducts(int $limit = 10): Collection
    {
        return $this->productRepository->getBestSellingProducts($limit);
    }

    public function uploadImages(int $productId, array $images): void
    {
        foreach ($images as $index => $image) {
            $path = $image->store('products', 'public');

            $this->imageRepository->create([
                'product_id' => $productId,
                'path' => $path,
                'alt_text' => $images[0]->getClientOriginalName(),
                'sort_order' => $index,
                'is_primary' => $index === 0,
            ]);
        }
    }

    public function deleteImage(int $imageId): bool
    {
        $image = $this->imageRepository->findById($imageId);
        Storage::disk('public')->delete($image->path);

        return $this->imageRepository->delete($imageId);
    }

    public function setPrimaryImage(int $productId, int $imageId): bool
    {
        return $this->imageRepository->setPrimary($productId, $imageId);
    }

    public function count(): int
    {
        return $this->productRepository->count();
    }
}
