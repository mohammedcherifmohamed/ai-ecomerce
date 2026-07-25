@extends('layouts.admin')
@section('title', 'Ready Emails')
@section('page-title', 'Ready Emails')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
        <select name="email_sent" class="form-select" style="width:auto;">
            <option value="">All</option>
            <option value="0" {{ request('email_sent') === '0' ? 'selected' : '' }}>Not Sent</option>
            <option value="1" {{ request('email_sent') === '1' ? 'selected' : '' }}>Sent</option>
        </select>
        <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
    </form>
    <div>
        <button class="btn btn-success" id="bulkSendBtn" disabled><i class="bi bi-enviar"></i> Bulk Send</button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>Title</th>
                        <th>Customer</th>
                        <th>Related Inquiry</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($readyEmails as $email)
                        <tr>
                            <td>
                                @if(!$email->email_sent)
                                    <input type="checkbox" class="email-checkbox" value="{{ $email->id }}">
                                @endif
                            </td>
                            <td><strong>{{ $email->title }}</strong></td>
                            <td>{{ $email->customer->name ?? ($email->customer->email ?? 'N/A') }}</td>
                            <td>
                                @if($email->inquiry)
                                    <a href="#" data-bs-toggle="tooltip" title="{{ $email->inquiry->inquiry }}">
                                        #{{ $email->inquiry->id }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($email->email_sent)
                                    <span class="badge bg-success">Sent</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                            <td>{{ $email->created_at->format('M d, Y') }}</td>
                            <td>
                                @if(!$email->email_sent)
                                    <button class="btn btn-sm btn-outline-primary edit-btn"
                                        data-id="{{ $email->id }}"
                                        data-title="{{ $email->title }}"
                                        data-email="{{ $email->email }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="{{ route('admin.ready-emails.send', $email->id) }}"
                                        class="btn btn-sm btn-outline-success"
                                        onclick="return confirm('Send this email?')">
                                        <i class="bi bi-send"></i>
                                    </a>
                                @else
                                    <span class="text-muted"><i class="bi bi-check-circle"></i> Sent</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No ready emails yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">{{ $readyEmails->withQueryString()->links() }}</div>

<form method="POST" action="{{ route('admin.ready-emails.send-bulk') }}" id="bulkForm">
    @csrf
    <input type="hidden" name="ids" id="bulkIds">
</form>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="editTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Content *</label>
                        <textarea class="form-control" id="editEmail" name="email" rows="12" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('editTitle').value = this.dataset.title;
            document.getElementById('editEmail').value = this.dataset.email;
            document.getElementById('editForm').action = '/admin/ready-emails/' + this.dataset.id;
        });
    });

    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.email-checkbox');
    const bulkBtn = document.getElementById('bulkSendBtn');

    function updateBulkBtn() {
        const checked = document.querySelectorAll('.email-checkbox:checked');
        bulkBtn.disabled = checked.length === 0;
    }

    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkBtn();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateBulkBtn));

    bulkBtn.addEventListener('click', function() {
        const checked = document.querySelectorAll('.email-checkbox:checked');
        if (checked.length === 0) return;
        if (!confirm(`Send ${checked.length} email(s)?`)) return;
        document.getElementById('bulkIds').value = Array.from(checked).map(cb => cb.value).join(',');
        document.getElementById('bulkForm').submit();
    });
</script>
@endsection