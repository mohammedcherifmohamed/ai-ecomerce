@extends('layouts.admin')
@section('title', 'Manage Documents')
@section('page-title', 'Documents')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
        <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
    </form>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="bi bi-upload"></i> Upload Document</button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Title</th><th>Type</th><th>File</th><th>Size</th><th>Uploaded By</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                        <tr>
                            <td><strong>{{ $document->title }}</strong></td>
                            <td><span class="badge bg-light text-dark">{{ $document->type->label() }}</span></td>
                            <td>{{ $document->file_name }}</td>
                            <td>{{ $document->formatted_size }}</td>
                            <td>{{ $document->uploader->name ?? 'N/A' }}</td>
                            <td>{{ $document->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.documents.download', $document->id) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-download"></i></a>
                                <form method="POST" action="{{ route('admin.documents.destroy', $document->id) }}" class="d-inline" onsubmit="return confirm('Delete this document?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No documents uploaded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">{{ $documents->withQueryString()->links() }}</div>

<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.documents.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Upload Document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type">
                            @foreach(\App\Enums\DocumentType::cases() as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">File *</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
