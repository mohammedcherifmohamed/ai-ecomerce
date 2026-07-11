@extends('layouts.employee')
@section('title', 'Manage Orders')
@section('page-title', 'Orders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search order #..." value="{{ request('search') }}">
        <select name="status" class="form-select" style="width:auto">
            <option value="">All Status</option>
            @foreach(\App\Enums\OrderStatus::cases() as $status)
                <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
    </form>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Order #</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><a href="{{ route('employee.orders.show', $order->id) }}"><strong>{{ $order->order_number }}</strong></a></td>
                            <td>{{ $order->customer->name ?? 'N/A' }}</td>
                            <td>{{ $order->items->count() }}</td>
                            <td class="price">${{ number_format($order->total_amount, 2) }}</td>
                            <td><span class="badge bg-{{ $order->status->color() }}">{{ $order->status->label() }}</span></td>
                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                            <td><a href="{{ route('employee.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">{{ $orders->withQueryString()->links() }}</div>
@endsection
