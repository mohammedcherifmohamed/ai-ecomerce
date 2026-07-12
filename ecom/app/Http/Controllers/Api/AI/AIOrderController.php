<?php

namespace App\Http\Controllers\Api\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\GetOrderStatusRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;

class AIOrderController extends Controller
{
    public function __construct( protected OrderService $orderService)
        {    }

    public function status(GetOrderStatusRequest $req){
        return response()->json(
            $this->orderService->getOrderStatusForAI(
                customerId: $req->customer_id,
                orderId: $req->order_id
            )
        );
    }
}
