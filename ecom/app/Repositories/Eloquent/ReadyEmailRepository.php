<?php

namespace App\Repositories\Eloquent;

use App\Models\ReadyEmail;
use App\Repositories\Interfaces\ReadyEmailRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReadyEmailRepository implements ReadyEmailRepositoryInterface
{
    public function __construct(
        protected ReadyEmail $model,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['inquiry', 'customer.user']);

        if (isset($filters['email_sent'])) {
            $query->where('email_sent', $filters['email_sent']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(int $id): ReadyEmail
    {
        return $this->model->with(['inquiry', 'customer.user'])->findOrFail($id);
    }

    public function update(int $id, array $data): ReadyEmail
    {
        $readyEmail = $this->findById($id);
        $readyEmail->update($data);
        return $readyEmail->fresh(['inquiry', 'customer.user']);
    }

    public function markAsSent(int $id): bool
    {
        return $this->model->where('id', $id)->update(['email_sent' => true]);
    }

    public function markBulkAsSent(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->update(['email_sent' => true]);
    }
}