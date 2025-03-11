@extends('layouts.dashboard')

@section('title', 'Competition Management')

@section('actions')
    <a href="{{ route('dashboard.competitions.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Create Competition
    </a>
@endsection

@section('content')
    <!-- Active Competitions -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Active & Upcoming Competitions</h5>
        </div>
        <div class="card-body">
            @if($activeCompetitions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Crossword</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Status</th>
                                <th>Participants</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeCompetitions as $competition)
                                <tr>
                                    <td>{{ $competition->id }}</td>
                                    <td>{{ $competition->title }}</td>
                                    <td>{{ $competition->crossword->title }}</td>
                                    <td>{{ $competition->start_time->format('M d, Y H:i') }}</td>
                                    <td>{{ $competition->end_time->format('M d, Y H:i') }}</td>
                                    <td>
                                        @if($competition->start_time > now())
                                            <span class="badge bg-info">Upcoming</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                    <td>{{ $competition->results_count ?? 0 }}</td>
                                    <td class="action-buttons">
                                        <a href="{{ route('dashboard.competitions.show', $competition) }}"
                                           class="btn btn-sm btn-info"
                                           title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if($competition->start_time > now() && (auth()->user()->isAdmin() || $competition->crossword->created_by === auth()->id()))
                                            <a href="{{ route('dashboard.competitions.edit', $competition) }}"
                                               class="btn btn-sm btn-primary"
                                               title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <form action="{{ route('dashboard.competitions.destroy', $competition) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                       class="btn btn-sm btn-danger"
                                                       title="Delete"
                                                       onclick="return confirm('Are you sure you want to delete this competition?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($competition->start_time <= now() && $competition->end_time >= now() && (auth()->user()->isAdmin() || $competition->crossword->created_by === auth()->id()))
                                            <form action="{{ route('dashboard.competitions.terminate', $competition) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                       class="btn btn-sm btn-warning"
                                                       title="Terminate"
                                                       onclick="return confirm('Are you sure you want to terminate this competition early?')">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-muted">No active or upcoming competitions found.</p>
            @endif
        </div>
    </div>

    <!-- Past Competitions -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Past Competitions</h5>
        </div>
        <div class="card-body">
            @if($pastCompetitions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Crossword</th>
                                <th>End Time</th>
                                <th>Duration</th>
                                <th>Participants</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pastCompetitions as $competition)
                                <tr>
                                    <td>{{ $competition->id }}</td>
                                    <td>{{ $competition->title }}</td>
                                    <td>{{ $competition->crossword->title }}</td>
                                    <td>{{ $competition->end_time->format('M d, Y H:i') }}</td>
                                    <td>
                                        {{ $competition->start_time->diffInHours($competition->end_time) }} hours
                                    </td>
                                    <td>{{ $competition->results_count ?? 0 }}</td>
                                    <td class="action-buttons">
                                        <a href="{{ route('dashboard.competitions.show', $competition) }}"
                                           class="btn btn-sm btn-info"
                                           title="View Results">
                                            <i class="bi bi-trophy"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $pastCompetitions->links() }}
                </div>
            @else
                <p class="text-center text-muted">No past competitions found.</p>
            @endif
        </div>
    </div>
@endsection
