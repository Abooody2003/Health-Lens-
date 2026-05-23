@extends('admin.layout')

@section('title', 'Specializations')
@section('page-title', 'Specializations Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Specializations</h1>
    <a href="{{ route('admin.specializations.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Specialization
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Specializations</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Doctors</th>
                        <th>Display Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($specializations as $specialization)
                        <tr>
                            <td>{{ $specialization->id }}</td>
                            <td>{{ $specialization->name }}</td>
                            <td><code>{{ $specialization->slug }}</code></td>
                            <td>
                                <span class="badge bg-info">{{ $specialization->doctors->count() }}</span>
                            </td>
                            <td>{{ $specialization->display_order }}</td>
                            <td>
                                <span class="badge {{ $specialization->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $specialization->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="table-actions">
                                <a href="{{ route('admin.specializations.show', $specialization) }}" class="btn btn-sm btn-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.specializations.edit', $specialization) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.specializations.destroy', $specialization) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this specialization? All associated doctors will be affected. This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-tags"></i>
                                    <h4>No Specializations Found</h4>
                                    <p>Get started by adding your first specialization.</p>
                                    <a href="{{ route('admin.specializations.create') }}" class="btn btn-primary mt-3">
                                        <i class="bi bi-plus-circle"></i> Add Specialization
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($specializations->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $specializations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

