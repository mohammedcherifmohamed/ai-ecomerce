@extends('layouts.employee')
@section('title', 'Order ' . $order->order_number)
@section('page-title', 'Order Details')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0">Order {{ $order->order_number }}</h6>
                <span class="badge bg-{{ $order->status->color() }} fs-6">{{ $order->status->label() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Product</th><th>SKU</th><th>Price</th><th>Qty</th><th>Total</th></tr></thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td><code>{{ $item->product_sku }}</code></td>
                                    <td>${{ number_format($item->unit_price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Status History</h6></div>
            <div class="card-body">
                @forelse($order->statusHistory as $history)
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <div>
                            <span class="badge bg-{{ \App\Enums\OrderStatus::from($history->status)->color() }}">{{ \App\Enums\OrderStatus::from($history->status)->label() }}</span>
                            @if($history->notes)<small class="text-muted ms-2">{{ $history->notes }}</small>@endif
                        </div>
                        <small class="text-muted">{{ $history->created_at->format('M d, Y H:i') }}</small>
                    </div>
                @empty
                    <p class="text-muted">No status history.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h6>Order Summary</h6>
                <hr>
                <div class="d-flex justify-content-between"><span>Subtotal</span><span>${{ number_format($order->subtotal, 2) }}</span></div>
                <div class="d-flex justify-content-between"><span>Tax</span><span>${{ number_format($order->tax_amount, 2) }}</span></div>
                <div class="d-flex justify-content-between"><span>Shipping</span><span>${{ number_format($order->shipping_amount, 2) }}</span></div>
                <hr>
                <div class="d-flex justify-content-between fw-bold"><span>Total</span><span class="price">${{ number_format($order->total_amount, 2) }}</span></div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Update Status</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('employee.orders.status', $order->id) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <select class="form-select" name="status" required>
                            @foreach(\App\Enums\OrderStatus::cases() as $status)
                                <option value="{{ $status->value }}" {{ $order->status === $status ? 'selected' : '' }}>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control" name="notes" placeholder="Notes..." rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Status</button>
                </form>
            </div>
        </div>

        @if($order->status->value !== 'cancelled' && $order->status->value !== 'refunded')
            <div class="card mb-4">
                <div class="card-body">
                    <form method="POST" action="{{ route('employee.orders.cancel', $order->id) }}" onsubmit="return confirm('Cancel this order?')">
                        @csrf
                        <button class="btn btn-outline-danger w-100">Cancel Order</button>
                    </form>
                    <form method="POST" action="{{ route('employee.orders.refund', $order->id) }}" class="mt-2" onsubmit="return confirm('Refund this order?')">
                        @csrf
                        <button class="btn btn-outline-warning w-100">Refund Order</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
