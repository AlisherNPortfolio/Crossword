@extends('layouts.dashboard')

@section('title', 'User Management')

@section('actions')
    <a href="{{ route('dashboard.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-1"></i> Create User
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
                            <th>Email</th>
                            <th>Status</th>
                            <th>Roles</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    @if($user->profile_photo)
                                        <img src="{{ Storage::url($user->profile_photo) }}"
                                             alt="{{ $user->name }}"
                                             class="rounded-circle me-2"
                                             width="30"
                                             height="30">
                                    @else
                                        <i class="bi bi-person-circle me-2"></i>
                                    @endif
                                    {{ $user->name }}
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-info">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                                </td>
                                <td class="action-buttons">
                                    <a href="{{ route('dashboard.users.show', $user) }}"
                                       class="btn btn-sm btn-info"
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('dashboard.users.edit', $user) }}"
                                       class="btn btn-sm btn-primary"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if(auth()->id() !== $user->id)
                                        <form action="{{ route('dashboard.users.destroy', $user) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                   class="btn btn-sm btn-danger"
                                                   title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection
