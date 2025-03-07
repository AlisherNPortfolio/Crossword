<!-- resources/views/competitions/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="mb-3">
                <a href="{{ route('competitions.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Competitions
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>{{ $competition->title }}</h2>
                </div>

                <div class="card-body">
                    @if($competition->description)
                        <div class="mb-4">
                            <p>{{ $competition->description }}</p>
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4>Competition Details</h4>
                            <dl class="row">
                                <dt class="col-sm-4">Crossword</dt>
                                <dd class="col-sm-8">
                                    {{ $competition->crossword->title }}
                                </dd>

                                <dt class="col-sm-4">Start Time</dt>
                                <dd class="col-sm-8">
                                    {{ $competition->start_time->format('F j, Y, g:i a') }}
                                </dd>

                                <dt class="col-sm-4">End Time</dt>
                                <dd class="col-sm-8">
                                    {{ $competition->end_time->format('F j, Y, g:i a') }}
                                </dd>

                                <dt class="col-sm-4">Status</dt>
                                <dd class="col-sm-8">
                                    @if($canPlay)
                                        <span class="badge bg-success">Active</span>
                                    @elseif($competition->start_time > now())
                                        <span class="badge bg-info">Upcoming</span>
                                    @else
                                        <span class="badge bg-secondary">Ended</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Duration</dt>
                                <dd class="col-sm-8">
                                    {{ $competition->start_time->diffInHours($competition->end_time) }} hours
                                </dd>
                            </dl>
                        </div>

                        <div class="col-md-6">
                            <h4>Your Status</h4>

                            @if($userResult)
                                <dl class="row">
                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8">
                                        @if($userResult->completed)
                                            <span class="badge bg-success">Completed</span>
                                        @else
                                            <span class="badge bg-warning">In Progress</span>
                                        @endif
                                    </dd>

                                    <dt class="col-sm-4">Score</dt>
                                    <dd class="col-sm-8">
                                        {{ $userResult->score }} points
                                    </dd>

                                    @if($userResult->completed)
                                        <dt class="col-sm-4">Time Taken</dt>
                                        <dd class="col-sm-8">
                                            {{ floor($userResult->time_taken / 60) }}:{{ str_pad($userResult->time_taken % 60, 2, '0', STR_PAD_LEFT) }}
                                        </dd>
                                    @endif

                                    @if($results && $userResult->ranking)
                                        <dt class="col-sm-4">Ranking</dt>
                                        <dd class="col-sm-8">
                                            {{ $userResult->ranking }} of {{ $results->count() }}
                                        </dd>
                                    @endif
                                </dl>
                            @else
                                <p>You haven't participated in this competition yet.</p>
                            @endif

                            <div class="mt-3">
                                @if($canPlay)
                                    <a href="{{ route('competitions.play', $competition) }}" class="btn btn-success">
                                        @if($userResult)
                                            Continue Competition
                                        @else
                                            Start Competition
                                        @endif
                                    </a>
                                @elseif($competition->start_time > now())
                                    <div class="alert alert-info">
                                        This competition hasn't started yet. It will begin on {{ $competition->start_time->format('F j, Y, g:i a') }}.
                                    </div>
                                @else
                                    <div class="alert alert-secondary">
                                        This competition has ended.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(auth()->user()->can('update', $competition) && $competition->is_active && $competition->end_time > now())
                        <div class="mb-4">
                            <form action="{{ route('competitions.terminate', $competition) }}" method="POST" onsubmit="return confirm('Are you sure you want to end this competition? This action cannot be undone.')">
                                @csrf
                                <button type="submit" class="btn btn-danger">Terminate Competition Early</button>
                            </form>
                        </div>
                    @endif

                    @if($results)
                        <h4 class="mb-3">Results</h4>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>User</th>
                                        <th>Score</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($results as $result)
                                        <tr class="{{ $result->user_id === auth()->id() ? 'table-info' : '' }}">
                                            <td>{{ $result->ranking }}</td>
                                            <td>
                                                {{ $result->user->name }}
                                                @if($result->user_id === auth()->id())
                                                    <span class="badge bg-success">You</span>
                                                @endif
                                            </td>
                                            <td>{{ $result->score }}</td>
                                            <td>
                                                {{ floor($result->time_taken / 60) }}:{{ str_pad($result->time_taken % 60, 2, '0', STR_PAD_LEFT) }}
                                            </td>
                                            <td>
                                                @if($result->completed)
                                                    <span class="badge bg-success">Completed</span>
                                                @else
                                                    <span class="badge bg-warning">Incomplete</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
