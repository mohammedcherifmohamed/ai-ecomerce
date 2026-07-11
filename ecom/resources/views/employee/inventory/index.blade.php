@extends('layouts.employee')
@section('title', 'Inventory Management')
@section('page-title', 'Inventory')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Stock Management</h5>
</div>

@if($lowStockProducts->count())
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> <strong>{{ $lowStockProducts->count() }}</strong> product(s) are low on stock.
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Product</th><th>SKU</th><th>Category</th><th>Stock</th><th>Threshold</th><th>Status</th><th>Update Stock</th></tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td><code>{{ $product->sku }}</code></td>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                            <td>
                                @if($product->isOutOfStock())
                                    <span class="badge bg-danger">{{ $product->stock_quantity }}</span>
                                @elseif($product->isLowStock())
                                    <span class="badge bg-warning">{{ $product->stock_quantity }}</span>
                                @else
                                    <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                                @endif
                            </td>
                            <td>{{ $product->low_stock_threshold }}</td>
                            <td>
                                @if($product->isOutOfStock())
                                    <span class="badge bg-danger">Out of Stock</span>
                                @elseif($product->isLowStock())
                                    <span class="badge bg-warning">Low Stock</span>
                                @else
                                    <span class="badge bg-success">In Stock</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('employee.inventory.update', $product->id) }}" class="d-flex gap-1">
                                    @csrf @method('PUT')
                                    <input type="number" name="stock_quantity" class="form-control form-control-sm" style="width:80px" value="{{ $product->stock_quantity }}" min="0">
                                    <button class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">{{ $products->links() }}</div>
@endsection
