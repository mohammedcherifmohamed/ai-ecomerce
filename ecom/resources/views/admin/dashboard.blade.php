@extends('layouts.admin')
@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card" style="border-color: #2563eb">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Revenue</p>
                        <h3 class="mb-0">${{ number_format($stats['total_revenue'], 2) }}</h3>
                    </div>
                    <i class="bi bi-currency-dollar display-5 text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-color: #16a34a">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Orders</p>
                        <h3 class="mb-0">{{ $stats['total_orders'] }}</h3>
                    </div>
                    <i class="bi bi-receipt display-5 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-color: #d97706">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Products</p>
                        <h3 class="mb-0">{{ $stats['total_products'] }}</h3>
                    </div>
                    <i class="bi bi-box-seam display-5 text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-color: #dc2626">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Customers</p>
                        <h3 class="mb-0">{{ $stats['total_customers'] }}</h3>
                    </div>
                    <i class="bi bi-people display-5 text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Recent Orders</h6>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            @forelse($stats['recent_orders'] as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order->id) }}">{{ $order->order_number }}</a></td>
                                    <td>{{ $order->customer->name ?? 'N/A' }}</td>
                                    <td>${{ number_format($order->total_amount, 2) }}</td>
                                    <td><span class="badge bg-{{ $order->status->color() }}">{{ $order->status->label() }}</span></td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
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

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Order Status</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span>Pending</span><span class="badge bg-warning">{{ $stats['pending_orders'] }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Processing</span><span class="badge bg-info">{{ $stats['processing_orders'] }}</span></div>
                <div class="d-flex justify-content-between"><span>Low Stock</span><span class="badge bg-danger">{{ $stats['low_stock_products'] }}</span></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Best Selling Products</h6></div>
            <div class="card-body">
                @forelse($stats['best_selling_products'] as $product)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>{{ $product->name }}</span>
                        <span class="badge bg-primary">{{ $product->total_sold ?? 0 }} sold</span>
                    </div>
                @empty
                    <p class="text-muted text-center">No sales data yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
