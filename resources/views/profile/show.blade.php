@extends('layouts.app')

@section('content')
<div class="container">
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
                    <p class="text-muted mb-4">{{ $user->profile->country ?? '' }}</p>

                    <div class="d-flex justify-content-center mb-2">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary me-2">Edit Profile</a>
                        <a href="{{ route('home') }}" class="btn btn-outline-primary">Back to Home</a>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">About</h5>
                </div>
                <div class="card-body">
                    <p>{{ $user->profile->bio ?? 'No bio added yet.' }}</p>

                    <div class="row">
                        <div class="col-sm-4">
                            <p class="mb-0">Member Since</p>
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
                            <p class="text-muted mb-0">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'N/A' }}</p>
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

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">User Roles</h5>
                </div>
                <div class="card-body">
                    @foreach($user->roles as $role)
                        <span class="badge bg-info me-1 mb-1">{{ $role->name }}</span>
                    @endforeach
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
                                <h1 class="text-primary">{{ $stats['crosswords_solved'] }}</h1>
                                <p class="mb-0">Crosswords Solved</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="stat-card p-3 border rounded text-center">
                                <h1 class="text-success">{{ $stats['competitions_participated'] }}</h1>
                                <p class="mb-0">Competitions Joined</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="stat-card p-3 border rounded text-center">
                                <h1 class="text-warning">{{ $stats['competitions_won'] }}</h1>
                                <p class="mb-0">Competitions Won</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="stat-card p-3 border rounded text-center">
                                <h1 class="text-info">{{ $stats['total_points'] }}</h1>
                                <p class="mb-0">Total Points</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Solutions</h5>
                    <a href="{{ route('profile.solutions') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Crossword</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSolutions as $solution)
                                    <tr>
                                        <td>
                                            <a href="{{ route('crosswords.show', $solution->crossword) }}">
                                                {{ $solution->crossword->title }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($solution->completed)
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-warning">In Progress</span>
                                            @endif
                                        </td>
                                        <td>{{ $solution->score }}</td>
                                        <td>{{ $solution->updated_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No solutions found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Competitions</h5>
                    <a href="{{ route('profile.competitions') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Competition</th>
                                    <th>Status</th>
                                    <th>Rank</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentCompetitions as $result)
                                    <tr>
                                        <td>
                                            <a href="{{ route('competitions.show', $result->competition) }}">
                                                {{ $result->competition->title }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($result->completed)
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-warning">In Progress</span>
                                            @endif
                                        </td>
                                        <td>{{ $result->ranking ?? 'N/A' }}</td>
                                        <td>{{ $result->score }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No competitions joined</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
