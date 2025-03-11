@extends('layouts.dashboard')

@section('title', 'Competition Details')

@section('actions')
    <div>
        @if($status === 'upcoming' && (auth()->user()->isAdmin() || $competition->crossword->created_by === auth()->id()))
            <a href="{{ route('dashboard.competitions.edit', $competition) }}" class="btn btn-primary me-2">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>

            <form action="{{ route('dashboard.competitions.destroy', $competition) }}" method="POST" class="d-inline-block me-2">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this competition?')">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </form>
        @endif

        @if($status === 'active' && (auth()->user()->isAdmin() || $competition->crossword->created_by === auth()->id()))
            <form action="{{ route('dashboard.competitions.terminate', $competition) }}" method="POST" class="d-inline-block me-2">
                @csrf
                <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to terminate this competition early?')">
                    <i class="bi bi-x-circle me-1"></i> Terminate
                </button>
            </form>
        @endif

        <a href="{{ route('dashboard.competitions.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Competitions
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Competition Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h2>{{ $competition->title }}</h2>
                        @if($competition->description)
                            <p>{{ $competition->description }}</p>
                        @endif

                        <div class="mb-2">
                            @if($status === 'upcoming')
                                <span class="badge bg-info">Upcoming</span>
                            @elseif($status === 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Ended</span>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6>Crossword</h6>
                                <p>{{ $competition->crossword->title }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6>Created By</h6>
                                <p>{{ $competition->crossword->creator->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6>Start Time</h6>
                                <p>{{ $competition->start_time->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6>End Time</h6>
                                <p>{{ $competition->end_time->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6>Duration</h6>
                                <p>{{ $competition->start_time->diffInHours($competition->end_time) }} hours</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6>Total Participants</h6>
                                <p>{{ $totalParticipants }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6>Completion Rate</h6>
                                <p>{{ number_format($completionRate, 1) }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Crossword Preview</h5>
                </div>
                <div class="card-body text-center">
                    <div class="crossword-grid-container">
                        <div class="crossword-grid">
                            @foreach($competition->crossword->grid_data as $rowIndex => $row)
                                <div class="crossword-row">
                                    @foreach($row as $colIndex => $cell)
                                        <div class="crossword-cell {{ $cell['letter'] ? 'has-letter' : '' }}">
                                            @if($cell['wordIndex'] !== null)
                                                <span class="word-index">{{ $cell['wordIndex'] }}</span>
                                            @endif

                                            @if($cell['letter'] !== null && $status !== 'upcoming')
                                                <span class="letter">{{ $cell['letter'] }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($status === 'upcoming')
                        <div class="mt-3">
                            <p class="text-muted"><i class="bi bi-info-circle me-1"></i> Crossword content is hidden until the competition starts.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Results</h5>
        </div>
        <div class="card-body">
            @if($status === 'upcoming')
                <div class="text-center py-4">
                    <p class="mb-0 text-muted">Results will be available once the competition starts.</p>
                </div>
            @elseif($results && $results->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>User</th>
                                <th>Score</th>
                                <th>Time Taken</th>
                                <th>Completion</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $result)
                                <tr>
                                    <td>
                                        @if($result->ranking == 1)
                                            <span class="badge bg-warning"><i class="bi bi-trophy"></i> {{ $result->ranking }}</span>
                                        @else
                                            {{ $result->ranking }}
                                        @endif
                                    </td>
                                    <td>{{ $result->user->name }}</td>
                                    <td>{{ $result->score }}</td>
                                    <td>
                                        @if($result->time_taken)
                                            {{ floor($result->time_taken / 60) }}:{{ str_pad($result->time_taken % 60, 2, '0', STR_PAD_LEFT) }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($result->completed)
                                            <span class="badge bg-success">Completed</span>
                                        @else
                                            <span class="badge bg-warning">Partial</span>
                                        @endif
                                    </td>
                                    <td>{{ $result->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <p class="mb-0 text-muted">No participants yet.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
