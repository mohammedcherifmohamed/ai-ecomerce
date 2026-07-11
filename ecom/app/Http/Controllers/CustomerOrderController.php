<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
    ) {}

    public function index(Request $request)
    {
        $customer = $request->user()->customer;

        if (!$customer) {
            return redirect()->route('home')->with('error', 'You do not have a customer profile.');
        }

        $orders = $this->orderService->getByCustomerId($customer->id, 15);

        return view('customer.orders.index', compact('orders'));
    }

    public function show(int $id)
    {
        $order = $this->orderService->getById($id);

        return view('customer.orders.show', compact('order'));
    }
}
