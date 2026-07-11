<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        protected Product $model,
    ) {}

    public function findById(int $id): Product
    {
        return $this->model->with(['category', 'images'])->findOrFail($id);
    }

    public function findBySlug(string $slug): Product
    {
        return $this->model->with(['category', 'images'])->where('slug', $slug)->firstOrFail();
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Product
    {
        $product = $this->findById($id);
        $product->update($data);

        return $product->fresh(['category', 'images']);
    }

    public function delete(int $id): bool
    {
        $product = $this->findById($id);

        return $product->delete();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['category', 'images']);

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('sku', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getActiveProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $filters['is_active'] = true;

        return $this->paginate($filters, $perPage);
    }

    public function getFeaturedProducts(int $limit = 10): Collection
    {
        return $this->model->with(['category', 'images'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->limit($limit)
            ->get();
    }

    public function updateStock(int $productId, int $quantity): bool
    {
        $product = $this->model->findOrFail($productId);

        return $product->update(['stock_quantity' => $quantity]);
    }

    public function decrementStock(int $productId, int $quantity): bool
    {
        $product = $this->model->findOrFail($productId);

        if ($product->stock_quantity < $quantity) {
            return false;
        }

        return $product->decrement('stock_quantity', $quantity);
    }

    public function incrementStock(int $productId, int $quantity): bool
    {
        $product = $this->model->findOrFail($productId);

        return $product->increment('stock_quantity', $quantity);
    }

    public function getLowStockProducts(): Collection
    {
        return $this->model->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->with('category')
            ->get();
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function getBestSellingProducts(int $limit = 10): Collection
    {
        return $this->model->with('category')
            ->select('products.*')
            ->addSelect(\DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM order_items WHERE product_id = products.id) as total_sold'))
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }
}
