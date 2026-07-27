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
        $customer = auth()->user()->customer;

        if (!$customer) {
            return response()->json(['error' => 'User is not a customer'], 403);
        }

        $inquiry = $this->inquiryService->create(
            inquiry: $request->inquiry,
            category: $request->category,
            customerId: $customer->id,
        );

        return response()->json($inquiry, 201);
    }
}
