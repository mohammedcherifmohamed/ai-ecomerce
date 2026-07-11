@extends('layouts.app')
@section('title', 'Home - ' . config('app.name'))

@section('content')
<section class="hero">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Welcome to {{ config('app.name') }}</h1>
        <p class="lead mb-4">Discover amazing products at great prices</p>
        <a href="{{ route('products.index') }}" class="btn btn-light btn-lg px-5">Shop Now</a>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Featured Products</h2>
        <div class="row g-4">
            @forelse($featuredProducts as $product)
                <div class="col-md-6 col-lg-3">
                    <div class="card card-product h-100">
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-secondary mb-2 align-self-start">{{ $product->category->name ?? 'Uncategorized' }}</span>
                            <h6 class="card-title">{{ $product->name }}</h6>
                            <p class="text-muted small flex-grow-1">{{ Str::limit($product->description, 80) }}</p>
                            <div class="mt-auto">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="price">${{ number_format($product->price, 2) }}</span>
                                    @if($product->compare_at_price)
                                        <span class="price-old">${{ number_format($product->compare_at_price, 2) }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('products.show', $product->slug) }}" class="btn btn-primary w-100 mt-2">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-5">
                    <i class="bi bi-box-seam display-1"></i>
                    <p class="mt-3">No featured products yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container text-center">
        <h2 class="mb-4">Why Shop With Us?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <i class="bi bi-truck display-4 text-primary"></i>
                <h5 class="mt-3">Free Shipping</h5>
                <p class="text-muted">On orders over $100</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-shield-check display-4 text-primary"></i>
                <h5 class="mt-3">Secure Payment</h5>
                <p class="text-muted">100% secure checkout</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-arrow-return-left display-4 text-primary"></i>
                <h5 class="mt-3">Easy Returns</h5>
                <p class="text-muted">30-day return policy</p>
            </div>
        </div>
    </div>
</section>
@endsection
