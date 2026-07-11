@extends('layouts.admin')
@section('title', isset($category) ? 'Edit Category' : 'Create Category')
@section('page-title', isset($category) ? 'Edit Category' : 'Create Category')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ isset($category) ? route('admin.categories.update', $category->id) : route('admin.categories.store') }}">
                    @csrf
                    @if(isset($category)) @method('PUT') @endif

                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $category->name ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $category->description ?? '') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="parent_id" class="form-label">Parent Category</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">None (Root Category)</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id ?? '') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ isset($category) ? 'Update Category' : 'Create Category' }}</button>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
