@extends('layouts.app')
@section('title', 'Products')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Products</h1>

    <form method="GET" action="{{ route('products.index') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="category_id" class="form-select">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" name="min_price" class="form-control" placeholder="Min $" value="{{ request('min_price') }}">
        </div>
        <div class="col-md-2">
            <input type="number" name="max_price" class="form-control" placeholder="Max $" value="{{ request('max_price') }}">
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
        </div>
    </form>

    <div class="row g-4">
        @forelse($products as $product)
            <div class="col-md-6 col-lg-4">
                <div class="card card-product h-100">
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-secondary mb-2 align-self-start">{{ $product->category->name ?? 'Uncategorized' }}</span>
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="text-muted small flex-grow-1">{{ Str::limit($product->description, 100) }}</p>
                        <div class="mt-auto">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="price">${{ number_format($product->price, 2) }}</span>
                                @if($product->compare_at_price)
                                    <span class="price-old">${{ number_format($product->compare_at_price, 2) }}</span>
                                @endif
                            </div>
                            @if($product->isOutOfStock())
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($product->isLowStock())
                                <span class="badge bg-warning">Low Stock ({{ $product->stock_quantity }})</span>
                            @else
                                <span class="badge bg-success">In Stock</span>
                            @endif
                            <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline-primary w-100 mt-2">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-search display-1 text-muted"></i>
                <p class="mt-3 text-muted">No products found matching your criteria.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">Clear Filters</a>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $products->withQueryString()->links() }}
    </div>
</div>
@endsection
