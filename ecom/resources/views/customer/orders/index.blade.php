@extends('layouts.app')
@section('title', 'My Orders')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">My Orders</h1>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td><strong>{{ $order->order_number }}</strong></td>
                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                        <td>{{ $order->items->count() }} item(s)</td>
                        <td class="price">${{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $order->status->color() }}">{{ $order->status->label() }}</span>
                        </td>
                        <td>
                            <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-receipt display-4"></i>
                            <p class="mt-2">You haven't placed any orders yet.</p>
                            <a href="{{ route('products.index') }}" class="btn btn-primary">Start Shopping</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $orders->links() }}
    </div>
</div>
@endsection
