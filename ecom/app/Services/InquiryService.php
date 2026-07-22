<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Repositories\Interfaces\InquiryRepositoryInterface;

class InquiryService
{
    public function __construct(
        protected InquiryRepositoryInterface $inquiryRepository,
    ) {}

    public function create(string $inquiry, ?string $category = null): Inquiry
    {
        return $this->inquiryRepository->create($inquiry, $category);
    }
}
