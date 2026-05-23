@extends('admin.layout')

@section('title', 'Specialization Details')
@section('page-title', 'Specialization Details')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Specialization Details</h1>
    <div>
        <a href="{{ route('admin.specializations.edit', $specialization) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('admin.specializations.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Specializations
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Specialization Information</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th width="200">ID</th>
                        <td>{{ $specialization->id }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $specialization->name }}</td>
                    </tr>
                    <tr>
                        <th>Slug</th>
                        <td><code>{{ $specialization->slug }}</code></td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $specialization->description ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Icon</th>
                        <td>{{ $specialization->icon ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Display Order</th>
                        <td>{{ $specialization->display_order }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge {{ $specialization->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $specialization->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Doctors Count</th>
                        <td>
                            <span class="badge bg-info">{{ $specialization->doctors->count() }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Created</th>
                        <td>{{ $specialization->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td>{{ $specialization->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    @if($specialization->doctors->count() > 0)
    <div class="col-md-12 mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Doctors in this Specialization</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($specialization->doctors as $doctor)
                                <tr>
                                    <td>{{ $doctor->id }}</td>
                                    <td>{{ $doctor->name }}</td>
                                    <td>{{ $doctor->email ?? 'N/A' }}</td>
                                    <td>{{ $doctor->phone_number ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $doctor->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $doctor->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.doctors.show', $doctor) }}" class="btn btn-sm btn-primary">
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
    @endif
</div>
@endsection

