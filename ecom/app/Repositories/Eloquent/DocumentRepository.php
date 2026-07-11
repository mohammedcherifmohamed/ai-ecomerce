<?php

namespace App\Repositories\Eloquent;

use App\Models\Document;
use App\Repositories\Interfaces\DocumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DocumentRepository implements DocumentRepositoryInterface
{
    public function __construct(
        protected Document $model,
    ) {}

    public function findById(int $id): Document
    {
        return $this->model->with('uploader')->findOrFail($id);
    }

    public function create(array $data): Document
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Document
    {
        $document = $this->findById($id);
        $document->update($data);

        return $document->fresh('uploader');
    }

    public function delete(int $id): bool
    {
        $document = $this->findById($id);

        return $document->delete();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with('uploader');

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getByUploader(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('uploaded_by', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function count(): int
    {
        return $this->model->count();
    }
}
