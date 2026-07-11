<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Services\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'department']);
        $employees = $this->employeeService->paginate($filters, 15);

        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.employees.create');
    }

    public function store(StoreEmployeeRequest $request)
    {
        $this->employeeService->create($request->validated());

        return redirect()->route('admin.employees.index')->with('success', 'Employee added successfully.');
    }

    public function edit(int $id)
    {
        $employee = $this->employeeService->getById($id);

        return view('admin.employees.create', compact('employee'));
    }

    public function update(UpdateEmployeeRequest $request, int $id)
    {
        $this->employeeService->update($id, $request->validated());

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->employeeService->delete($id);

        return redirect()->route('admin.employees.index')->with('success', 'Employee removed.');
    }
}
