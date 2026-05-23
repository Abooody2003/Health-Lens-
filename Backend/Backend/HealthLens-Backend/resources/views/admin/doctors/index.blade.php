@extends('admin.layout')

@section('title', 'Doctors')
@section('page-title', 'Doctors Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Doctors</h1>
    <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Doctor
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Doctors</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Specialization</th>
                        <th>City</th>
                        <th>Area</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                        <tr>
                            <td>{{ $doctor->id }}</td>
                            <td>{{ $doctor->name }}</td>
                            <td>{{ $doctor->specialization->name ?? 'N/A' }}</td>
                            <td>{{ $doctor->city ?? 'N/A' }}</td>
                            <td>{{ $doctor->area ?? 'N/A' }}</td>
                            <td>{{ $doctor->email ?? 'N/A' }}</td>
                            <td>{{ $doctor->phone_number ?? 'N/A' }}</td>
                            <td>
                                <span class="badge {{ $doctor->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $doctor->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="table-actions">
                                <a href="{{ route('admin.doctors.show', $doctor) }}" class="btn btn-sm btn-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.doctors.edit', $doctor) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.doctors.destroy', $doctor) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this doctor? This action cannot be undone.');">
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
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="bi bi-person-badge"></i>
                                    <h4>No Doctors Found</h4>
                                    <p>Get started by adding your first doctor.</p>
                                    <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary mt-3">
                                        <i class="bi bi-plus-circle"></i> Add Doctor
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($doctors->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $doctors->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

