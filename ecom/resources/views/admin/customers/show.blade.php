@extends('layouts.admin')
@section('title', 'Customer Details')
@section('page-title', 'Customer Details')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <i class="bi bi-person-circle display-1 text-muted"></i>
                <h5 class="mt-2">{{ $customer->user->name ?? 'N/A' }}</h5>
                <p class="text-muted">{{ $customer->user->email ?? '' }}</p>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Contact Info</h6></div>
            <div class="card-body">
                <p><i class="bi bi-telephone"></i> {{ $customer->phone ?? 'N/A' }}</p>
                <p><i class="bi bi-geo-alt"></i> {{ $customer->address ?? 'N/A' }}</p>
                <p><i class="bi bi-building"></i> {{ $customer->city ?? '' }}, {{ $customer->state ?? '' }} {{ $customer->zip_code ?? '' }}</p>
                <p><i class="bi bi-globe"></i> {{ $customer->country }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Purchase History</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($customer->orders as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order->id) }}">{{ $order->order_number }}</a></td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>{{ $order->items->count() }}</td>
                                    <td>${{ number_format($order->total_amount, 2) }}</td>
                                    <td><span class="badge bg-{{ $order->status->color() }}">{{ $order->status->label() }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No orders yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
