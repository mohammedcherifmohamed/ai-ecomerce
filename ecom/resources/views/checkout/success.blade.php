@extends('layouts.app')
@section('title', 'Order Placed!')

@section('content')
<div class="container py-5 text-center">
    <div class="mb-4">
        <i class="bi bi-check-circle-fill text-success display-1"></i>
    </div>
    <h1 class="mb-3">Order Placed Successfully!</h1>
    <p class="lead text-muted">Thank you for your purchase. Your order number is <strong>{{ $order->order_number }}</strong>.</p>

    <div class="row justify-content-center mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <table class="table mb-0">
                        <tr>
                            <td class="text-start">Order #</td>
                            <td class="text-end"><strong>{{ $order->order_number }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-start">Total</td>
                            <td class="text-end price">${{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-start">Status</td>
                            <td class="text-end"><span class="badge bg-{{ $order->status->color() }}">{{ $order->status->label() }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="mt-4 d-flex gap-3 justify-content-center">
                <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-primary"><i class="bi bi-eye"></i> View Order</a>
                <a href="{{ route('products.index') }}" class="btn btn-outline-primary"><i class="bi bi-bag"></i> Continue Shopping</a>
            </div>
        </div>
    </div>
</div>
@endsection
