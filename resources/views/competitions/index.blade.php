<!-- resources/views/competitions/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Crossword Competitions</h2>
                    <a href="{{ route('competitions.create') }}" class="btn btn-primary">Create Competition</a>
                </div>

                <div class="card-body">
                    @if($activeCompetitions->count() > 0)
                        <div class="mb-5">
                            <h3 class="mb-3">Active Competitions</h3>
                            <div class="row">
                                @foreach($activeCompetitions as $competition)
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100 bg-light">
                                            <div class="card-body">
                                                <h4 class="card-title">{{ $competition->title }}</h4>
                                                @if($competition->description)
                                                    <p class="card-text">{{ \Illuminate\Support\Str::limit($competition->description, 100) }}</p>
                                                @endif
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        Ends: {{ $competition->end_time->format('F j, Y, g:i a') }}
                                                    </small>
                                                </p>
                                            </div>
                                            <div class="card-footer bg-transparent">
                                                <a href="{{ route('competitions.show', $competition) }}" class="btn btn-success w-100">Join Competition</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($upcomingCompetitions->count() > 0)
                        <div class="mb-5">
                            <h3 class="mb-3">Upcoming Competitions</h3>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Crossword</th>
                                            <th>Starts</th>
                                            <th>Duration</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($upcomingCompetitions as $competition)
                                            <tr>
                                                <td>{{ $competition->title }}</td>
                                                <td>{{ $competition->crossword->title }}</td>
                                                <td>{{ $competition->start_time->format('F j, Y, g:i a') }}</td>
                                                <td>
                                                    {{ $competition->start_time->diffInHours($competition->end_time) }} hours
                                                </td>
                                                <td>
                                                    <a href="{{ route('competitions.show', $competition) }}" class="btn btn-sm btn-info">Details</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <div>
                        <h3 class="mb-3">Past Competitions</h3>

                        @if($pastCompetitions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Crossword</th>
                                            <th>Ended</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pastCompetitions as $competition)
                                            <tr>
                                                <td>{{ $competition->title }}</td>
                                                <td>{{ $competition->crossword->title }}</td>
                                                <td>{{ $competition->end_time->format('F j, Y, g:i a') }}</td>
                                                <td>
                                                    <span class="badge bg-secondary">Ended</span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('competitions.show', $competition) }}" class="btn btn-sm btn-info">Results</a>
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
                            <div class="text-center">
                                <p class="text-muted">No past competitions available.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
