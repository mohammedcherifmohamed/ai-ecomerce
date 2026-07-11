@extends('layouts.admin')
@section('title', 'Manage Categories')
@section('page-title', 'Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">All Categories</h5>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Category</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Name</th><th>Slug</th><th>Parent</th><th>Products</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td><strong>{{ $category->name }}</strong></td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td>{{ $category->parent->name ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark">{{ $category->products_count ?? 0 }}</span></td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category->id) }}" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">{{ $categories->withQueryString()->links() }}</div>
@endsection
