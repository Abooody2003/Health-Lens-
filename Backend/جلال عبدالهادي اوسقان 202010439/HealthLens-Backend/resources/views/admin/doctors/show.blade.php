@extends('admin.layout')

@section('title', 'Doctor Details')
@section('page-title', 'Doctor Details')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Doctor Details</h1>
    <div>
        <a href="{{ route('admin.doctors.edit', $doctor) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('admin.doctors.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Doctors
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Doctor Information</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th width="200">ID</th>
                        <td>{{ $doctor->id }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $doctor->name }}</td>
                    </tr>
                    <tr>
                        <th>Specialization</th>
                        <td>
                            @if($doctor->specialization)
                                <a href="{{ route('admin.specializations.show', $doctor->specialization) }}">
                                    {{ $doctor->specialization->name }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $doctor->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Phone Number</th>
                        <td>{{ $doctor->phone_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>City</th>
                        <td>{{ $doctor->city ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Area</th>
                        <td>{{ $doctor->area ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>{{ $doctor->address ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge {{ $doctor->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $doctor->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Created</th>
                        <td>{{ $doctor->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td>{{ $doctor->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

