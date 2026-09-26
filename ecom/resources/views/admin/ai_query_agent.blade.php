@extends('layouts.admin')
@section('title', 'AI SQL Query Agent')
@section('page-title', 'AI SQL Query Agent (MiniCPM)')

@section('styles')
<style>
    .query-form { max-width: 800px; }
    .job-card { transition: all 0.2s; }
    .job-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.1); }
    .status-badge { font-size: 0.75rem; }
    .sql-display { background: #1e1e1e; color: #d4d4d4; border-radius: 6px; padding: 1rem; font-family: 'Monaco', 'Menlo', monospace; font-size: 0.875rem; max-height: 300px; overflow: auto; white-space: pre-wrap; }
    .result-table { font-size: 0.8125rem; }
    .result-table th, .result-table td { padding: 0.5rem; }
    .spinner-border-sm { width: 1rem; height: 1rem; }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-terminal me-2"></i>Natural Language to SQL</h5>
                <span class="badge bg-{{ $aiServiceStatus ? 'success' : 'danger' }} fs-6">
                    <i class="bi bi-circle-fill me-1"></i>{{ $aiServiceStatus ? 'AI Service Online' : 'AI Service Offline' }}
                </span>
            </div>
            <div class="card-body">
                <form id="queryForm" class="query-form">
                    <div class="mb-3">
                        <label for="queryInput" class="form-label fw-medium">Enter your question in natural language</label>
                        <textarea id="queryInput" name="query" class="form-control" rows="3" placeholder="e.g., Show me all customers who placed orders in the last 30 days" required></textarea>
                        <div class="form-text">The AI will convert your question to SQL, execute it, and return a natural language response.</div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-send me-1"></i> Submit Query
                    </button>
                    <button type="button" class="btn btn-outline-secondary ms-2" id="clearBtn">Clear</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12" id="jobStatusCard" style="display: none;">
        <div class="card mb-4 job-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-hourglass-split me-2"></i>Processing Query...</h5>
                <span class="badge bg-info status-badge" id="currentJobStatus">Pending</span>
            </div>
            <div class="card-body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted" id="statusMessage">Converting to SQL and executing...</p>
                    <small class="text-muted" id="jobIdDisplay"></small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12" id="resultCard" style="display: none;">
        <div class="card mb-4 job-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Query Result</h5>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="newQueryBtn">
                        <i class="bi bi-plus me-1"></i> New Query
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Response:</strong>
                    <div class="p-3 bg-light rounded mt-2" id="responseContent"></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Generated SQL:</strong>
                        <div class="sql-display" id="sqlContent"></div>
                    </div>
                    <div class="col-md-6">
                        <strong>Query Result:</strong>
                        <div id="resultContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Queries</h5>
            </div>
            <div class="card-body p-0">
                @if($recentJobs->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-clock display-4 d-block mb-2"></i>
                        No queries yet. Submit your first query above!
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Query</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Completed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentJobs as $job)
                                    <tr>
                                        <td>
                                            <div class="text-truncate" style="max-width: 300px;" title="{{ $job->user_input }}">
                                                {{ $job->user_input }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $job->status === 'completed' ? 'success' : 
                                                ($job->status === 'failed' ? 'danger' : 
                                                ($job->status === 'processing' ? 'info' : 'warning')) }} status-badge">
                                                {{ ucfirst($job->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $job->created_at->format('M d, Y H:i') }}</td>
                                        <td>{{ $job->completed_at ? $job->completed_at->format('M d, Y H:i') : '-' }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary view-result-btn" 
                                                    data-job-id="{{ $job->id }}"
                                                    data-status="{{ $job->status }}"
                                                    {{ $job->status !== 'completed' && $job->status !== 'failed' ? 'disabled' : '' }}>
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const queryForm = document.getElementById('queryForm');
    const queryInput = document.getElementById('queryInput');
    const submitBtn = document.getElementById('submitBtn');
    const clearBtn = document.getElementById('clearBtn');
    const newQueryBtn = document.getElementById('newQueryBtn');
    const jobStatusCard = document.getElementById('jobStatusCard');
    const resultCard = document.getElementById('resultCard');
    const currentJobStatus = document.getElementById('currentJobStatus');
    const statusMessage = document.getElementById('statusMessage');
    const jobIdDisplay = document.getElementById('jobIdDisplay');
    const responseContent = document.getElementById('responseContent');
    const sqlContent = document.getElementById('sqlContent');
    const resultContent = document.getElementById('resultContent');
    let currentJobId = null;
    let pollInterval = null;
    let pollCount = 0;
    const MAX_POLL_COUNT = 60;

    queryForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const query = queryInput.value.trim();
        if (!query) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting...';

        try {
            const response = await fetch('{{ route("admin.ai.query.submit") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ query })
            });

            const data = await response.json();

            if (response.ok) {
                currentJobId = data.job_id;
                showJobStatus(data.job_id);
                pollJobStatus(data.job_id);
            } else {
                alert(data.message || 'Failed to submit query');
            }
        } catch (err) {
            console.error(err);
            alert('Error submitting query');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-send me-1"></i> Submit Query';
        }
    });

    clearBtn.addEventListener('click', () => {
        queryInput.value = '';
    });

    newQueryBtn.addEventListener('click', () => {
        resultCard.style.display = 'none';
        queryForm.style.display = 'block';
        queryInput.value = '';
        queryInput.focus();
    });

    function showJobStatus(jobId) {
        queryForm.style.display = 'none';
        jobStatusCard.style.display = 'block';
        jobIdDisplay.textContent = 'Job ID: ' + jobId;
    }

    async function pollJobStatus(jobId) {
        if (pollInterval) clearInterval(pollInterval);
        pollCount = 0;
        
        pollInterval = setInterval(async () => {
            pollCount++;
            if (pollCount >= MAX_POLL_COUNT) {
                clearInterval(pollInterval);
                showError({ error: 'Timeout: Query took too long to process' });
                return;
            }
            try {
                const response = await fetch(`{{ route("admin.ai.query.status", ["job" => "__JOB_ID__"]) }}`.replace('__JOB_ID__', jobId));
                const data = await response.json();

                if (data.status === 'pending') {
                    currentJobStatus.textContent = 'Pending';
                    currentJobStatus.className = 'badge bg-warning status-badge';
                    statusMessage.textContent = 'Waiting to process...';
                } else if (data.status === 'processing') {
                    currentJobStatus.textContent = 'Processing';
                    currentJobStatus.className = 'badge bg-info status-badge';
                    statusMessage.textContent = 'Executing query...';
                } else if (data.status === 'completed') {
                    clearInterval(pollInterval);
                    showResult(data);
                } else if (data.status === 'failed') {
                    clearInterval(pollInterval);
                    showError(data);
                }
            } catch (err) {
                console.error(err);
            }
        }, 2000);
    }

    function showResult(data) {
        jobStatusCard.style.display = 'none';
        resultCard.style.display = 'block';

        responseContent.textContent = data.result || 'No response generated';
        sqlContent.textContent = data.generated_sql || 'No SQL generated';
        
        if (data.query_result && data.query_result.rows) {
            renderResultTable(data.query_result);
        } else {
            resultContent.innerHTML = '<p class="text-muted">No data returned</p>';
        }
    }

    function showError(data) {
        jobStatusCard.style.display = 'none';
        resultCard.style.display = 'block';

        responseContent.innerHTML = '<div class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>' + (data.error || 'Query failed') + '</div>';
        sqlContent.textContent = data.generated_sql || 'No SQL generated';
        resultContent.innerHTML = '<p class="text-muted">No data returned</p>';
    }

    function renderResultTable(result) {
        if (!result.rows || result.rows.length === 0) {
            resultContent.innerHTML = '<p class="text-muted">No rows returned</p>';
            return;
        }

        const columns = Object.keys(result.rows[0]);
        let html = '<div class="table-responsive"><table class="table table-sm table-striped result-table">';
        html += '<thead><tr>';
        columns.forEach(col => html += '<th>' + col + '</th>');
        html += '</tr></thead><tbody>';
        
        result.rows.forEach(row => {
            html += '<tr>';
            columns.forEach(col => {
                const val = row[col];
                html += '<td>' + (val !== null && val !== undefined ? val : '<span class="text-muted">NULL</span>') + '</td>';
            });
            html += '</tr>';
        });
        
        html += '</tbody></table></div>';
        html += '<small class="text-muted">' + result.row_count + ' row(s) returned</small>';
        resultContent.innerHTML = html;
    }

    document.querySelectorAll('.view-result-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const jobId = btn.dataset.jobId;
            try {
                const response = await fetch(`{{ route("admin.ai.query.status", ["job" => "__JOB_ID__"]) }}`.replace('__JOB_ID__', jobId));
                const data = await response.json();
                if (data.status === 'completed' || data.status === 'failed') {
                    showResult(data);
                }
            } catch (err) {
                console.error(err);
            }
        });
    });
</script>
@endsection