<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected ProductService $productService,
    ) {}

    public function buyNow(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $customer = $request->user()->customer;

        $order = $this->orderService->create(
            [
                'customer_id' => $customer->id,
            ],
            [
                [
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity,
                ],
            ]
        );

        return redirect()->route('checkout.success', $order->id)->with('success', 'Order placed successfully!');
    }

    public function success(int $id)
    {
        $order = $this->orderService->getById($id);

        return view('checkout.success', compact('order'));
    }
}
