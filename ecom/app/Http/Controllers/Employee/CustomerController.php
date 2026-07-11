<?php

namespace App\Http\Controllers\Employee;

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

        return view('employee.customers.index', compact('customers'));
    }
}
