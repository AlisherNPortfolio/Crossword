@extends('layouts.dashboard')

@section('title', 'Create Role')

@section('actions')
    <a href="{{ route('dashboard.roles.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Roles
    </a>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-card">
                <form method="POST" action="{{ route('dashboard.roles.store') }}">
                    @csrf

                    <div class="form-section">
                        <h3 class="form-section-title">Role Information</h3>

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            <div class="form-text">The display name of the role.</div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            <div class="form-text">A brief description of the role's purpose.</div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Permissions</h3>

                        @foreach($permissions as $group => $groupPermissions)
                            <div class="permission-group">
                                <h4 class="permission-group-title text-capitalize">{{ $group }} Permissions</h4>

                                <div class="row">
                                    @foreach($groupPermissions as $permission)
                                        <div class="col-md-6">
                                            <div class="form-check permission-checkbox">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}"
                                                    {{ (is_array(old('permissions')) && in_array($permission->id, old('permissions'))) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                    <small class="text-muted d-block">{{ $permission->description }}</small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        @error('permissions')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('dashboard.roles.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
