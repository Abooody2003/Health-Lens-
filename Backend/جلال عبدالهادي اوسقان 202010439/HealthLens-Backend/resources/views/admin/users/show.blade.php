@extends('admin.layout')

@section('title', 'User Details')
@section('page-title', 'User Details')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>User Details</h1>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th width="200">ID</th>
                        <td>{{ $user->id }}</td>
                    </tr>
                    <tr>
                        <th>Full Name</th>
                        <td>{{ $user->full_name }}</td>
                    </tr>
                    <tr>
                        <th>First Name</th>
                        <td>{{ $user->first_name }}</td>
                    </tr>
                    <tr>
                        <th>Last Name</th>
                        <td>{{ $user->last_name }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th>Username</th>
                        <td>{{ $user->username ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td>
                            <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-primary' }}">
                                {{ ucfirst($user->role ?? 'user') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Date of Birth</th>
                        <td>{{ $user->date_of_birth ? $user->date_of_birth->format('M d, Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Gender</th>
                        <td>{{ ucfirst($user->gender ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <th>Plan</th>
                        <td>{{ ucfirst($user->plan ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <th>Email Verified</th>
                        <td>
                            @if($user->email_verified_at)
                                <span class="badge bg-success">Verified</span>
                                <small class="text-muted">({{ $user->email_verified_at->format('M d, Y') }})</small>
                            @else
                                <span class="badge bg-warning">Not Verified</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Registered</th>
                        <td>{{ $user->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td>{{ $user->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Statistics</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Surgery Analyses:</strong>
                    <span class="badge bg-primary">{{ $user->surgeryAnalyses->count() }}</span>
                </div>
                <div class="mb-3">
                    <strong>Chats:</strong>
                    <span class="badge bg-info">{{ $user->chats->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Surgery Analyses -->
@if($user->surgeryAnalyses->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Surgery Analyses</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->surgeryAnalyses as $analysis)
                                <tr>
                                    <td>{{ $analysis->id }}</td>
                                    <td>{{ $analysis->age }}</td>
                                    <td>{{ ucfirst($analysis->gender) }}</td>
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

