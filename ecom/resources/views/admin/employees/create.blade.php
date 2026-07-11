@extends('layouts.admin')
@section('title', isset($employee) ? 'Edit Employee' : 'Add Employee')
@section('page-title', isset($employee) ? 'Edit Employee' : 'Add Employee')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ isset($employee) ? route('admin.employees.update', $employee->id) : route('admin.employees.store') }}">
                    @csrf
                    @if(isset($employee)) @method('PUT') @endif

                    @if(!isset($employee))
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>
                    @else
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $employee->user->name ?? '') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $employee->user->email ?? '') }}" required>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" class="form-control" id="department" name="department" value="{{ old('department', $employee->department ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="position" class="form-label">Position</label>
                            <input type="text" class="form-control" id="position" name="position" value="{{ old('position', $employee->position ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="hire_date" class="form-label">Hire Date *</label>
                            <input type="date" class="form-control" id="hire_date" name="hire_date" value="{{ old('hire_date', isset($employee) ? $employee->hire_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ isset($employee) ? 'Update Employee' : 'Add Employee' }}</button>
                        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
