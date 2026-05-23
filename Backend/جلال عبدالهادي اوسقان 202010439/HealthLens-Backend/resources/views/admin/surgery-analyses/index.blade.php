@extends('admin.layout')

@section('title', 'Surgery Analyses')
@section('page-title', 'Surgery Analyses Management')

@section('content')
<div class="page-header">
    <h1>Surgery Analyses</h1>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Surgery Analyses</h5>
    </div>
    <div class="card-body">
        <!-- Filter -->
        <div class="mb-3">
            <form method="GET" action="{{ route('admin.surgery-analyses.index') }}" class="d-flex gap-2">
                <select name="status" class="form-select" style="width: auto;">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if(request('status'))
                    <a href="{{ route('admin.surgery-analyses.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Kmax</th>
                        <th>CCT</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($analyses as $analysis)
                        <tr>
                            <td>{{ $analysis->id }}</td>
                            <td>{{ $analysis->user->full_name ?? 'N/A' }}</td>
                            <td>{{ $analysis->age }}</td>
                            <td>{{ ucfirst($analysis->gender) }}</td>
                            <td>{{ $analysis->kmax }}</td>
                            <td>{{ $analysis->cct }}</td>
                            <td>
                                @if($analysis->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($analysis->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($analysis->status === 'processing')
                                    <span class="badge bg-info">Processing</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                @endif
                            </td>
                            <td>{{ $analysis->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.surgery-analyses.show', $analysis) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="bi bi-clipboard-data"></i>
                                    <h4>No Surgery Analyses Found</h4>
                                    <p>
                                        @if(request('status'))
                                            No analyses found with status: <strong>{{ ucfirst(request('status')) }}</strong>
                                        @else
                                            There are no surgery analyses in the system yet.
                                        @endif
                                    </p>
                                    @if(request('status'))
                                        <a href="{{ route('admin.surgery-analyses.index') }}" class="btn btn-secondary mt-3">
                                            <i class="bi bi-arrow-left"></i> View All Analyses
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($analyses->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $analyses->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

