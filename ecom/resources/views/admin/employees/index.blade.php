@extends('layouts.admin')
@section('title', 'Manage Employees')
@section('page-title', 'Employees')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
        <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Employee</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Name</th><th>Email</th><th>Employee ID</th><th>Department</th><th>Position</th><th>Hire Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td><strong>{{ $employee->user->name ?? 'N/A' }}</strong></td>
                            <td>{{ $employee->user->email ?? 'N/A' }}</td>
                            <td><code>{{ $employee->employee_id }}</code></td>
                            <td>{{ $employee->department ?? '-' }}</td>
                            <td>{{ $employee->position ?? '-' }}</td>
                            <td>{{ $employee->hire_date->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('admin.employees.destroy', $employee->id) }}" class="d-inline" onsubmit="return confirm('Remove this employee?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No employees found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">{{ $employees->withQueryString()->links() }}</div>
@endsection
