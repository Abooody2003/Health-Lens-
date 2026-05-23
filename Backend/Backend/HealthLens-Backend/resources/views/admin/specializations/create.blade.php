@extends('admin.layout')

@section('title', 'Create Specialization')
@section('page-title', 'Create Specialization')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Create Specialization</h1>
    <a href="{{ route('admin.specializations.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Specializations
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Specialization Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.specializations.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="icon" class="form-label">Icon</label>
                <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" value="{{ old('icon') }}" placeholder="e.g., bi-heart-pulse">
                <small class="form-text text-muted">Bootstrap Icons class name</small>
                @error('icon')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="display_order" class="form-label">Display Order</label>
                <input type="number" class="form-control @error('display_order') is-invalid @enderror" id="display_order" name="display_order" value="{{ old('display_order') }}" min="0">
                @error('display_order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Create Specialization
                </button>
                <a href="{{ route('admin.specializations.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

