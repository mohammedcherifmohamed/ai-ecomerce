<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        protected Category $model,
    ) {}

    public function findById(int $id): Category
    {
        return $this->model->with(['parent', 'children', 'products'])->findOrFail($id);
    }

    public function findBySlug(string $slug): Category
    {
        return $this->model->where('slug', $slug)->firstOrFail();
    }

    public function create(array $data): Category
    {
        if (! isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->model->create($data);
    }

    public function update(int $id, array $data): Category
    {
        $category = $this->findById($id);

        if (isset($data['name']) && ! isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return $category->fresh(['parent', 'children']);
    }

    public function delete(int $id): bool
    {
        $category = $this->findById($id);

        return $category->delete();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['parent', 'children']);

        if (isset($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('sort_order')->orderBy('name')->paginate($perPage);
    }

    public function getActiveCategories(): Collection
    {
        return $this->model->where('is_active', true)
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();
    }

    public function getRootCategories(): Collection
    {
        return $this->model->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();
    }

    public function count(): int
    {
        return $this->model->count();
    }
}
