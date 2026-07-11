@extends('layouts.admin')
@section('title', isset($product) ? 'Edit Product' : 'Create Product')
@section('page-title', isset($product) ? 'Edit Product' : 'Create Product')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($product) ? route('admin.products.update', $product->id) : route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf
            @if(isset($product)) @method('PUT') @endif

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $product->name ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $product->description ?? '') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price *</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" value="{{ old('price', $product->price ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="compare_at_price" class="form-label">Compare at Price</label>
                            <input type="number" class="form-control" id="compare_at_price" name="compare_at_price" step="0.01" value="{{ old('compare_at_price', $product->compare_at_price ?? '') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="sku" class="form-label">SKU *</label>
                            <input type="text" class="form-control" id="sku" name="sku" value="{{ old('sku', $product->sku ?? '') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="low_stock_threshold" class="form-label">Low Stock Threshold</label>
                            <input type="number" class="form-control" id="low_stock_threshold" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 10) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="weight" class="form-label">Weight (kg)</label>
                            <input type="number" class="form-control" id="weight" name="weight" step="0.01" value="{{ old('weight', $product->weight ?? '') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">Featured</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header"><h6 class="mb-0">Images</h6></div>
                        <div class="card-body">
                            <input type="file" class="form-control mb-2" name="images[]" multiple accept="image/*">
                            <small class="text-muted">Upload up to 5 images (max 5MB each)</small>

                            @if(isset($product) && $product->images->count())
                                <div class="mt-3">
                                    @foreach($product->images as $image)
                                        <div class="position-relative d-inline-block me-2 mb-2">
                                            <img src="{{ asset('storage/' . $image->path) }}" width="80" height="80" class="rounded" style="object-fit:cover">
                                            @if($image->is_primary)
                                                <span class="badge bg-primary position-absolute top-0 start-0">Primary</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ isset($product) ? 'Update Product' : 'Create Product' }}</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
