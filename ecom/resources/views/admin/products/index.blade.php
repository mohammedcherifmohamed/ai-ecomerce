@extends('layouts.admin')
@section('title', 'Manage Products')
@section('page-title', 'Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
        <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Product</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Name</th><th>SKU</th><th>Price</th><th>Stock</th><th>Category</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td><code>{{ $product->sku }}</code></td>
                            <td>${{ number_format($product->price, 2) }}</td>
                            <td>
                                @if($product->isOutOfStock())
                                    <span class="badge bg-danger">{{ $product->stock_quantity }}</span>
                                @elseif($product->isLowStock())
                                    <span class="badge bg-warning">{{ $product->stock_quantity }}</span>
                                @else
                                    <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                                @endif
                            </td>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
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

<div class="mt-3 d-flex justify-content-center">{{ $products->withQueryString()->links() }}</div>
@endsection
