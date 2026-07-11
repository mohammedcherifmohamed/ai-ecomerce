<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'search']);
        $orders = $this->orderService->paginate($filters, 15);

        return view('employee.orders.index', compact('orders'));
    }

    public function show(int $id)
    {
        $order = $this->orderService->getById($id);

        return view('employee.orders.show', compact('order'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id)
    {
        $this->orderService->updateStatus($id, $request->status, $request->notes, $request->user()->id);

        return redirect()->route('employee.orders.show', $id)->with('success', 'Order status updated.');
    }

    public function cancel(Request $request, int $id)
    {
        $this->orderService->cancel($id, 'Cancelled by employee', $request->user()->id);

        return redirect()->route('employee.orders.show', $id)->with('success', 'Order cancelled.');
    }

    public function refund(Request $request, int $id)
    {
        $this->orderService->refund($id, 'Refunded by employee', $request->user()->id);

        return redirect()->route('employee.orders.show', $id)->with('success', 'Order refunded.');
    }
}
