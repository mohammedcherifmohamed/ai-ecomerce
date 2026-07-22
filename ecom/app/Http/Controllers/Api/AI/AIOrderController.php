<?php

namespace App\Http\Controllers\Api\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\CancelOrderRequest;
use App\Http\Requests\AI\GetOrderStatusRequest;
use App\Http\Requests\AI\CreateInquiryRequest;
use App\Models\Inquiry;
use App\Services\InquiryService;
use App\Services\OrderService;

class AIOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected InquiryService $inquiryService,
    ) {}

    public function status(GetOrderStatusRequest $req) {
        return response()->json(
            $this->orderService->getOrderStatusForAI(
                customerId: $req->customer_id,
                orderId: $req->order_id
            )
        );
    }

    public function cancel(CancelOrderRequest $req) {
        return response()->json(
            $this->orderService->cancelOrderForAI(
                customerId: $req->customer_id,
                orderId: $req->order_id
            )
        );
    }

    public function createInquiry(CreateInquiryRequest $req) {
        return response()->json(
            $this->inquiryService->create(
                inquiry: $req->inquiry,
                category: $req->category,
            )
        );
    }
}
