@extends('layouts.dashboard')

@section('title', 'Role Management')

@section('actions')
    <a href="{{ route('dashboard.roles.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Create Role
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Users</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                            <tr>
                                <td>{{ $role->id }}</td>
                                <td>{{ $role->name }}</td>
                                <td><code>{{ $role->slug }}</code></td>
                                <td>{{ $role->description }}</td>
                                <td>{{ $role->users_count }}</td>
                                <td>{{ $role->permissions_count }}</td>
                                <td class="action-buttons">
                                    @if(!in_array($role->slug, ['administrator', 'creator', 'solver']))
                                        <a href="{{ route('dashboard.roles.edit', $role) }}"
                                           class="btn btn-sm btn-primary"
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('dashboard.roles.destroy', $role) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                   class="btn btn-sm btn-danger"
                                                   title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this role?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted">Default role</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
