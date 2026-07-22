<?php

namespace App\Repositories\Interfaces;

use App\Models\Inquiry;

interface InquiryRepositoryInterface
{
    public function create(string $inquiry, ?string $category = null): Inquiry;
}
