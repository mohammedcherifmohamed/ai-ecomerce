@extends('layouts.app')
@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('customer.orders.index') }}">My Orders</a></li>
            <li class="breadcrumb-item active">{{ $order->order_number }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order {{ $order->order_number }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product_name }}</td>
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
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h6>Order Summary</h6>
                    <hr>
                    <div class="d-flex justify-content-between"><span>Subtotal</span><span>${{ number_format($order->subtotal, 2) }}</span></div>
                    <div class="d-flex justify-content-between"><span>Tax</span><span>${{ number_format($order->tax_amount, 2) }}</span></div>
                    <div class="d-flex justify-content-between"><span>Shipping</span><span>${{ number_format($order->shipping_amount, 2) }}</span></div>
                    @if($order->discount_amount > 0)
                        <div class="d-flex justify-content-between text-success"><span>Discount</span><span>-${{ number_format($order->discount_amount, 2) }}</span></div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between fw-bold"><span>Total</span><span class="price">${{ number_format($order->total_amount, 2) }}</span></div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h6>Status</h6>
                    <span class="badge bg-{{ $order->status->color() }} fs-6">{{ $order->status->label() }}</span>
                    @if($order->shipped_at)
                        <p class="mt-2 text-muted small">Shipped: {{ $order->shipped_at->format('M d, Y') }}</p>
                    @endif
                    @if($order->delivered_at)
                        <p class="text-muted small">Delivered: {{ $order->delivered_at->format('M d, Y') }}</p>
                    @endif
                </div>
            </div>

            @if($order->shipping_address)
                <div class="card">
                    <div class="card-body">
                        <h6>Shipping Address</h6>
                        <p class="mb-0">{{ $order->shipping_address }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
