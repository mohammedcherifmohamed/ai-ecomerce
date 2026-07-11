<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $customers = $this->customerService->paginate($filters, 15);

        return view('admin.customers.index', compact('customers'));
    }

    public function show(int $id)
    {
        $customer = $this->customerService->getById($id)->load('orders.items');

        return view('admin.customers.show', compact('customer'));
    }

    public function destroy(int $id)
    {
        $this->customerService->delete($id);

        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted.');
    }
}
