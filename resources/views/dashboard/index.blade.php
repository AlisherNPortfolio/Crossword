@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
    <div class="row">
        <!-- Stats Cards -->
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon bg-primary text-white">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
                <div class="stats-number">{{ $stats['total_users'] }}</div>
                <div class="stats-title">Total Users</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon bg-success text-white">
                    <i class="bi bi-grid-3x3-gap fs-4"></i>
                </div>
                <div class="stats-number">{{ $stats['total_crosswords'] }}</div>
                <div class="stats-title">Total Crosswords</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon bg-info text-white">
                    <i class="bi bi-trophy fs-4"></i>
                </div>
                <div class="stats-number">{{ $stats['total_competitions'] }}</div>
                <div class="stats-title">Total Competitions</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon bg-warning text-white">
                    <i class="bi bi-check2-square fs-4"></i>
                </div>
                <div class="stats-number">{{ $stats['total_solutions'] }}</div>
                <div class="stats-title">Puzzle Solutions</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Crosswords -->
        <div class="col-md-6">
            <div class="recent-activity-card">
                <div class="card-header">
                    <h5 class="card-title">Recent Crosswords</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Creator</th>
                                <th>Published</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCrosswords as $crossword)
                                <tr>
                                    <td>
                                        <a href="{{ route('dashboard.crosswords.show', $crossword) }}">
                                            {{ $crossword->title }}
                                        </a>
                                    </td>
                                    <td>{{ $crossword->creator->name }}</td>
                                    <td>
                                        @if($crossword->published)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $crossword->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No crosswords found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('dashboard.crosswords.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    <a href="{{ route('dashboard.crosswords.create') }}" class="btn btn-sm btn-primary">Create New</a>
                </div>
            </div>
        </div>

        <!-- Recent Competitions -->
        <div class="col-md-6">
            <div class="recent-activity-card">
                <div class="card-header">
                    <h5 class="card-title">Recent Competitions</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Crossword</th>
                                <th>Status</th>
                                <th>Period</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCompetitions as $competition)
                                <tr>
                                    <td>
                                        <a href="{{ route('dashboard.competitions.show', $competition) }}">
                                            {{ $competition->title }}
                                        </a>
                                    </td>
                                    <td>{{ $competition->crossword->title }}</td>
                                    <td>
                                        @if($competition->is_active && $competition->start_time <= now() && $competition->end_time >= now())
                                            <span class="badge bg-success">Active</span>
                                        @elseif($competition->is_active && $competition->start_time > now())
                                            <span class="badge bg-info">Upcoming</span>
                                        @else
                                            <span class="badge bg-secondary">Ended</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $competition->start_time->format('M d') }} - {{ $competition->end_time->format('M d') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No competitions found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('dashboard.competitions.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    <a href="{{ route('dashboard.competitions.create') }}" class="btn btn-sm btn-primary">Create New</a>
                </div>
            </div>
        </div>
    </div>
@endsection
