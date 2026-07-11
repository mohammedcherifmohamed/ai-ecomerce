@extends('layouts.app')
@section('title', $product->name)

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
            @if($product->category)
                <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-7">
            @if($product->images->count())
                <img src="{{ asset('storage/' . $product->images->first()->path) }}" class="img-fluid rounded" alt="{{ $product->name }}">
                @if($product->images->count() > 1)
                    <div class="row mt-3 g-2">
                        @foreach($product->images as $image)
                            <div class="col-3">
                                <img src="{{ asset('storage/' . $image->path) }}" class="img-fluid rounded" alt="{{ $image->alt_text ?? $product->name }}" style="height:80px;object-fit:cover;width:100%">
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height:400px">
                    <i class="bi bi-image display-1 text-muted"></i>
                </div>
            @endif
        </div>
        <div class="col-md-5">
            <span class="badge bg-secondary mb-2">{{ $product->category->name ?? 'Uncategorized' }}</span>
            <h1 class="mb-3">{{ $product->name }}</h1>
            <p class="text-muted">SKU: {{ $product->sku }}</p>

            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="price fs-3">${{ number_format($product->price, 2) }}</span>
                @if($product->compare_at_price)
                    <span class="price-old fs-5">${{ number_format($product->compare_at_price, 2) }}</span>
                @endif
            </div>

            <div class="mb-3">
                @if($product->isOutOfStock())
                    <span class="badge bg-danger fs-6">Out of Stock</span>
                @elseif($product->isLowStock())
                    <span class="badge bg-warning fs-6">Only {{ $product->stock_quantity }} left in stock</span>
                @else
                    <span class="badge bg-success fs-6">In Stock ({{ $product->stock_quantity }} available)</span>
                @endif
            </div>

            <div class="mb-4">
                <h5>Description</h5>
                <p>{{ $product->description ?? 'No description available.' }}</p>
            </div>

            @if($product->weight)
                <p class="text-muted"><i class="bi bi-box"></i> Weight: {{ $product->weight }} kg</p>
            @endif

            @if(!$product->isOutOfStock())
            <form method="POST" action="{{ route('checkout.buy-now') }}" class="mt-4">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="d-flex align-items-center gap-3">
                    <div class="input-group" style="width:130px">
                        <button class="btn btn-outline-secondary" type="button" onclick="this.nextElementSibling.stepDown()">-</button>
                        <input type="number" name="quantity" class="form-control text-center" value="1" min="1" max="{{ $product->stock_quantity }}">
                        <button class="btn btn-outline-secondary" type="button" onclick="this.previousElementSibling.stepUp()">+</button>
                    </div>
                    @auth
                    <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                        <i class="bi bi-cart-check"></i> Buy Now
                    </button>
                    @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg flex-grow-1">
                        <i class="bi bi-cart-check"></i> Login to Buy
                    </a>
                    @endauth
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
