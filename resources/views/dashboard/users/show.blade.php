@extends('layouts.dashboard')

@section('title', 'User Details')

@section('actions')
    <div>
        <a href="{{ route('dashboard.users.edit', $user) }}" class="btn btn-primary me-2">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('dashboard.users.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Users
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    @if($user->profile_photo)
                        <img src="{{ Storage::url($user->profile_photo) }}" class="rounded-circle img-fluid" style="width: 150px;">
                    @else
                        <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                            <i class="bi bi-person-fill" style="font-size: 5rem; color: #6c757d;"></i>
                        </div>
                    @endif

                    <h3 class="my-3">{{ $user->name }}</h3>
                    <p class="text-muted mb-1">{{ $user->email }}</p>

                    <div class="mb-2">
                        @if($user->active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>

                    <div class="d-flex flex-wrap justify-content-center mb-2">
                        @foreach($user->roles as $role)
                            <span class="badge bg-info m-1">{{ $role->name }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <p class="mb-0">Joined</p>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted mb-0">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <p class="mb-0">Last Login</p>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted mb-0">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <p class="mb-0">Country</p>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted mb-0">{{ $user->profile->country ?? 'Not specified' }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <p class="mb-0">Language</p>
                        </div>
                        <div class="col-sm-8">
                            <p class="text-muted mb-0">{{ $user->profile->language ?? 'Not specified' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="stat-card p-3 border rounded text-center">
                                <h1 class="text-primary">{{ $stats['crosswords_created'] }}</h1>
                                <p class="mb-0">Crosswords Created</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="stat-card p-3 border rounded text-center">
                                <h1 class="text-success">{{ $stats['crosswords_published'] }}</h1>
                                <p class="mb-0">Crosswords Published</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="stat-card p-3 border rounded text-center">
                                <h1 class="text-warning">{{ $stats['crosswords_solved'] }}</h1>
                                <p class="mb-0">Crosswords Solved</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="stat-card p-3 border rounded text-center">
                                <h1 class="text-info">{{ $stats['competitions_participated'] }}</h1>
                                <p class="mb-0">Competitions Joined</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Created Crosswords</h5>
                </div>
                <div class="card-body">
                    @if($user->crosswords->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Published</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->crosswords->take(5) as $crossword)
                                        <tr>
                                            <td>{{ $crossword->title }}</td>
                                            <td>
                                                @if($crossword->published)
                                                    <span class="badge bg-success">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                            <td>{{ $crossword->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('dashboard.crosswords.show', $crossword) }}" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($user->crosswords->count() > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('dashboard.crosswords.index', ['user' => $user->id]) }}" class="btn btn-sm btn-outline-primary">
                                    View All Crosswords
                                </a>
                            </div>
                        @endif
                    @else
                        <p class="text-center text-muted">This user hasn't created any crosswords yet.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    @if($user->solutions->count() > 0 || $user->competitionResults->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($user->solutions->sortByDesc('updated_at')->take(3) as $solution)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-grid-3x3-gap me-2 text-primary"></i>
                                        Solved crossword: <strong>{{ $solution->crossword->title }}</strong>
                                        @if($solution->completed)
                                            <span class="badge bg-success ms-2">Completed</span>
                                        @else
                                            <span class="badge bg-warning ms-2">In Progress</span>
                                        @endif
                                    </div>
                                    <span class="text-muted">{{ $solution->updated_at->format('M d, Y') }}</span>
                                </li>
                            @endforeach

                            @foreach($user->competitionResults->sortByDesc('updated_at')->take(3) as $result)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-trophy me-2 text-warning"></i>
                                        Participated in competition: <strong>{{ $result->competition->title }}</strong>
                                        @if($result->completed)
                                            <span class="badge bg-success ms-2">Completed</span>
                                        @else
                                            <span class="badge bg-warning ms-2">In Progress</span>
                                        @endif
                                    </div>
                                    <span class="text-muted">{{ $result->updated_at->format('M d, Y') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-center text-muted">No recent activity found for this user.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
