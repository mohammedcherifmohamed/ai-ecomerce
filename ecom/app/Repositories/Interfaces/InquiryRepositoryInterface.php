<?php

namespace App\Repositories\Interfaces;

use App\Models\Inquiry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface InquiryRepositoryInterface
{
    public function create(string $inquiry, ?string $category = null): Inquiry;

    public function search(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getCategoryCounts(?string $dateFrom = null, ?string $dateTo = null): Collection;

    public function getDailyCounts(?string $dateFrom = null, ?string $dateTo = null): Collection;
}
