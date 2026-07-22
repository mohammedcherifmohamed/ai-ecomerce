<?php

namespace App\Http\Controllers;

use App\Http\Requests\AI\CreateInquiryRequest;
use App\Services\InquiryService;

class InquiryController extends Controller
{
    public function __construct(
        protected InquiryService $inquiryService,
    ) {}

    public function store(CreateInquiryRequest $request)
    {
        $inquiry = $this->inquiryService->create(
            inquiry: $request->inquiry,
            category: $request->category,
        );

        return response()->json($inquiry, 201);
    }
}
