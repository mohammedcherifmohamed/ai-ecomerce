@extends('layouts.employee')
@section('title', 'Customers')
@section('page-title', 'Customers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
        <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
    </form>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Name</th><th>Email</th><th>Phone</th><th>City</th><th>Orders</th><th>Joined</th></tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td><strong>{{ $customer->user->name ?? 'N/A' }}</strong></td>
                            <td>{{ $customer->user->email ?? 'N/A' }}</td>
                            <td>{{ $customer->phone ?? '-' }}</td>
                            <td>{{ $customer->city ?? '-' }}</td>
                            <td><span class="badge bg-primary">{{ $customer->orders->count() }}</span></td>
                            <td>{{ $customer->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">{{ $customers->withQueryString()->links() }}</div>
@endsection
