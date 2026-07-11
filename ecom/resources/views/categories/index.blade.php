@extends('layouts.app')
@section('title', 'Categories')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Categories</h1>
    <div class="row g-4">
        @forelse($categories as $category)
            <div class="col-md-4 col-lg-3">
                <div class="card card-product h-100 text-center">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <i class="bi bi-folder display-3 text-primary mb-3"></i>
                        <h5 class="card-title">{{ $category->name }}</h5>
                        <p class="text-muted small">{{ Str::limit($category->description, 60) }}</p>
                        <span class="badge bg-light text-dark">{{ $category->products_count ?? $category->products->count() }} products</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-folder display-1 text-muted"></i>
                <p class="mt-3 text-muted">No categories available.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
