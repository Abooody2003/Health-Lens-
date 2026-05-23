@extends('admin.layout')

@section('title', 'Edit Doctor')
@section('page-title', 'Edit Doctor')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Edit Doctor</h1>
    <a href="{{ route('admin.doctors.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Doctors
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Doctor Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.doctors.update', $doctor) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $doctor->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="specialization_id" class="form-label">Specialization <span class="text-danger">*</span></label>
                <select class="form-select @error('specialization_id') is-invalid @enderror" id="specialization_id" name="specialization_id" required>
                    <option value="">Select Specialization</option>
                    @foreach($specializations as $specialization)
                        <option value="{{ $specialization->id }}" {{ old('specialization_id', $doctor->specialization_id) == $specialization->id ? 'selected' : '' }}>
                            {{ $specialization->name }}
                        </option>
                    @endforeach
                </select>
                @error('specialization_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $doctor->email) }}">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number', $doctor->phone_number) }}">
                @error('phone_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $doctor->city) }}" required>
                @error('city')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="area" class="form-label">Area <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('area') is-invalid @enderror" id="area" name="area" value="{{ old('area', $doctor->area) }}" required>
                @error('area')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address', $doctor->address) }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $doctor->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Doctor
                </button>
                <a href="{{ route('admin.doctors.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

