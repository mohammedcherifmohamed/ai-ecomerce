<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Repositories\Interfaces\InquiryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InquiryService
{
    public function __construct(
        protected InquiryRepositoryInterface $inquiryRepository,
    ) {}

    public function create(string $inquiry, ?string $category = null): Inquiry
    {
        return $this->inquiryRepository->create($inquiry, $category);
    }

    public function search(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->inquiryRepository->search($filters, $perPage);
    }

    public function getCategoryCounts(?string $dateFrom = null, ?string $dateTo = null): Collection
    {
        return $this->inquiryRepository->getCategoryCounts($dateFrom, $dateTo);
    }

    public function getDailyCounts(?string $dateFrom = null, ?string $dateTo = null): Collection
    {
        return $this->inquiryRepository->getDailyCounts($dateFrom, $dateTo);
    }
}
