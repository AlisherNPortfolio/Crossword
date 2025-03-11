@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card bg-primary text-white">
                <div class="card-body p-5 text-center">
                    <h1 class="display-4 fw-bold mb-4">Welcome to the Crossword Generator</h1>
                    <p class="lead mb-4">Create, solve, and compete in custom crossword puzzles</p>
                    @guest
                        <div class="d-flex gap-3 justify-content-center">
                            <a href="{{ route('login') }}" class="btn btn-light btn-lg">Sign In</a>
                            <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">Register</a>
                        </div>
                    @else
                        <div class="d-flex gap-3 justify-content-center">
                            <a href="{{ route('crosswords.index') }}" class="btn btn-light btn-lg">Browse Crosswords</a>
                            <a href="{{ route('competitions.index') }}" class="btn btn-outline-light btn-lg">Join Competitions</a>
                            @if(auth()->user()->hasPermission('crosswords.create'))
                                <a href="{{ route('crosswords.create') }}" class="btn btn-success btn-lg">Create Crossword</a>
                            @endif
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="row mb-5">
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-grid-3x3-gap display-4 text-primary mb-3"></i>
                    <h3>Generate Crosswords</h3>
                    <p class="text-muted">Create custom crossword puzzles with your own words and clues.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-puzzle display-4 text-success mb-3"></i>
                    <h3>Solve Puzzles</h3>
                    <p class="text-muted">Challenge yourself with crosswords created by the community.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-trophy display-4 text-warning mb-3"></i>
                    <h3>Join Competitions</h3>
                    <p class="text-muted">Participate in timed competitions and climb the leaderboard.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Crosswords Section -->
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="m-0">Latest Crosswords</h2>
                </div>
                <div class="card-body">
                    @if($latestCrosswords->count() > 0)
                        <div class="row">
                            @foreach($latestCrosswords as $crossword)
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h4 class="mb-1">{{ $crossword->title }}</h4>
                                            <p class="text-muted mb-2">Created by {{ $crossword->creator->name }} on {{ $crossword->created_at->format('M d, Y') }}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="badge bg-primary me-1">{{ count($crossword->words) }} Words</span>
                                                    <span class="badge bg-secondary">{{ count($crossword->grid_data) }}x{{ count($crossword->grid_data[0]) }}</span>
                                                </div>
                                                <a href="{{ route('crosswords.show', $crossword) }}" class="btn btn-outline-primary">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-center">
                            <a href="{{ route('crosswords.index') }}" class="btn btn-primary">Browse All Crosswords</a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="mb-3">No crosswords available yet.</p>
                            @if(auth()->check() && auth()->user()->hasPermission('crosswords.create'))
                                <a href="{{ route('crosswords.create') }}" class="btn btn-primary">Create First Crossword</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="row">
        <div class="col-md-12">
            <div class="card bg-light">
                <div class="card-body p-5 text-center">
                    <h2 class="mb-3">Ready to get started?</h2>
                    <p class="lead mb-4">Join our community of crossword creators and solvers today!</p>
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Create an Account</a>
                    @else
                        @if(auth()->user()->hasPermission('crosswords.create'))
                            <a href="{{ route('crosswords.create') }}" class="btn btn-primary btn-lg">Create Your Crossword</a>
                        @else
                            <a href="{{ route('crosswords.index') }}" class="btn btn-primary btn-lg">Start Solving</a>
                        @endif
                    @endguest
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
