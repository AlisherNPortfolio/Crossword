@extends('layouts.dashboard')

@section('title', 'Crossword Details')

@section('actions')
    <div>
        @if(auth()->user()->isAdmin() || auth()->id() === $crossword->created_by)
            @if($crossword->solutions()->count() == 0)
                <a href="{{ route('dashboard.crosswords.edit', $crossword) }}" class="btn btn-primary me-2">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            @endif

            <form action="{{ route('dashboard.crosswords.toggle-publish', $crossword) }}" method="POST" class="d-inline-block me-2">
                @csrf
                <button type="submit" class="btn {{ $crossword->published ? 'btn-warning' : 'btn-success' }}">
                    <i class="bi {{ $crossword->published ? 'bi-eye-slash' : 'bi-eye' }} me-1"></i>
                    {{ $crossword->published ? 'Unpublish' : 'Publish' }}
                </button>
            </form>

            @if($crossword->solutions()->count() == 0 && $crossword->competitions()->count() == 0)
                <form action="{{ route('dashboard.crosswords.destroy', $crossword) }}" method="POST" class="d-inline-block me-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this crossword?')">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            @endif
        @endif

        <a href="{{ route('dashboard.crosswords.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Crosswords
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Crossword Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h2>{{ $crossword->title }}</h2>
                        <p class="text-muted">
                            Created by {{ $crossword->creator->name }} on {{ $crossword->created_at->format('F j, Y') }}
                        </p>
                        <div>
                            @if($crossword->published)
                                <span class="badge bg-success">Published</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6>Number of Words</h6>
                                <p class="fs-4">{{ count($crossword->words) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6>Grid Size</h6>
                                <p class="fs-4">{{ count($crossword->grid_data) }} x {{ count($crossword->grid_data[0]) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6>Solution Attempts</h6>
                                <p class="fs-4">{{ $totalAttempts }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6>Completion Rate</h6>
                                <p class="fs-4">{{ number_format($completionRate, 1) }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Word List</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Across</h6>
                            <ul class="list-group">
                                @foreach($crossword->words as $word)
                                    @if($word['orientation'] === 'horizontal')
                                        <li class="list-group-item">
                                            <strong>{{ $word['index'] }}.</strong> {{ $word['word'] }}
                                            <p class="text-muted mb-0 small">{{ $word['clue'] }}</p>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Down</h6>
                            <ul class="list-group">
                                @foreach($crossword->words as $word)
                                    @if($word['orientation'] === 'vertical')
                                        <li class="list-group-item">
                                            <strong>{{ $word['index'] }}.</strong> {{ $word['word'] }}
                                            <p class="text-muted mb-0 small">{{ $word['clue'] }}</p>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
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
                            @foreach($crossword->grid_data as $rowIndex => $row)
                                <div class="crossword-row">
                                    @foreach($row as $colIndex => $cell)
                                        <div class="crossword-cell {{ $cell['letter'] ? 'has-letter' : '' }}">
                                            @if($cell['wordIndex'] !== null)
                                                <span class="word-index">{{ $cell['wordIndex'] }}</span>
                                            @endif

                                            @if($cell['letter'] !== null)
                                                <span class="letter">{{ $cell['letter'] }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Top Solvers</h5>
                </div>
                <div class="card-body">
                    @if(count($completions) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Score</th>
                                        <th>Time</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($completions as $solution)
                                        <tr>
                                            <td>{{ $solution->user->name }}</td>
                                            <td>{{ $solution->score }}</td>
                                            <td>{{ floor($solution->time_taken / 60) }}:{{ str_pad($solution->time_taken % 60, 2, '0', STR_PAD_LEFT) }}</td>
                                            <td>{{ $solution->updated_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-muted">No completed solutions yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
