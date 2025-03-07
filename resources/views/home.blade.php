<!-- resources/views/home.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Welcome to the Crossword Generator</h2>
                </div>

                <div class="card-body">
                    <div class="mb-4">
                        <h3>Latest Crosswords</h3>
                        @if($latestCrosswords->count() > 0)
                            <div class="list-group">
                                @foreach($latestCrosswords as $crossword)
                                    <a href="{{ route('crosswords.show', $crossword) }}" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1">{{ $crossword->title }}</h5>
                                            <small>{{ $crossword->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1">Created by: {{ $crossword->creator->name }}</p>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No crosswords available yet. Be the first to create one!</p>
                        @endif
                    </div>

                    <div class="d-flex justify-content-center mb-3">
                        <a href="{{ route('crosswords.index') }}" class="btn btn-primary me-2">Browse All Crosswords</a>
                        <a href="{{ route('crosswords.create') }}" class="btn btn-success">Create New Crossword</a>
                    </div>

                    <hr>

                    <div class="mt-3">
                        <h3>Competitions</h3>
                        <p>Test your crossword solving skills against other players in timed competitions!</p>
                        <div class="text-center">
                            <a href="{{ route('competitions.index') }}" class="btn btn-info">View Competitions</a>
                        </div>
                    </div>

                    <hr>

                    <div class="mt-3">
                        <h3>Leaderboard</h3>
                        <p>See the top crossword solvers and compete for the highest score!</p>
                        <div class="text-center">
                            <a href="{{ route('crosswords.leaderboard') }}" class="btn btn-secondary">View Leaderboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
