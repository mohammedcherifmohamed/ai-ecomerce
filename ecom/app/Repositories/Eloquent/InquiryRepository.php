<?php

namespace App\Repositories\Eloquent;

use App\Models\Inquiry;
use App\Repositories\Interfaces\InquiryRepositoryInterface;

class InquiryRepository implements InquiryRepositoryInterface
{
    public function __construct(
        protected Inquiry $model,
    ) {}

    public function create(string $inquiry, ?string $category = null): Inquiry
    {
        return $this->model->create([
            'inquiry' => $inquiry,
            'category' => $category,
        ]);
    }
}
