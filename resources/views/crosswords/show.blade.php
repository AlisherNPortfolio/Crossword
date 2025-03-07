<!-- resources/views/crosswords/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="mb-3">
                <a href="{{ route('crosswords.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Crosswords
                </a>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>{{ $crossword->title }}</h2>
                    <a href="{{ route('crosswords.play', $crossword) }}" class="btn btn-success">Play Now</a>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Crossword Details</h4>
                            <dl class="row">
                                <dt class="col-sm-4">Created By</dt>
                                <dd class="col-sm-8">{{ $crossword->creator->name }}</dd>

                                <dt class="col-sm-4">Created On</dt>
                                <dd class="col-sm-8">{{ $crossword->created_at->format('F j, Y') }}</dd>

                                <dt class="col-sm-4">Number of Words</dt>
                                <dd class="col-sm-8">{{ count($crossword->words) }}</dd>

                                <dt class="col-sm-4">Grid Size</dt>
                                <dd class="col-sm-8">{{ count($crossword->grid_data) }} x {{ count($crossword->grid_data[0]) }}</dd>
                            </dl>

                            @if($userSolution)
                                <div class="mt-4">
                                    <h4>Your Progress</h4>
                                    <dl class="row">
                                        <dt class="col-sm-4">Status</dt>
                                        <dd class="col-sm-8">
                                            @if($userSolution->completed)
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-warning">In Progress</span>
                                            @endif
                                        </dd>

                                        @if($userSolution->completed)
                                            <dt class="col-sm-4">Time Taken</dt>
                                            <dd class="col-sm-8">
                                                {{ floor($userSolution->time_taken / 60) }}:{{ str_pad($userSolution->time_taken % 60, 2, '0', STR_PAD_LEFT) }}
                                            </dd>

                                            <dt class="col-sm-4">Score</dt>
                                            <dd class="col-sm-8">
                                                {{ $userSolution->score }} points
                                            </dd>
                                        @endif

                                        <dt class="col-sm-4">Last Played</dt>
                                        <dd class="col-sm-8">
                                            {{ $userSolution->updated_at->diffForHumans() }}
                                        </dd>
                                    </dl>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <h4>Preview</h4>
                            <div class="crossword-preview">
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

                            <div class="mt-4">
                                <h4>Clues</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Across</h5>
                                        <ul class="list-group">
                                            @foreach($crossword->words as $word)
                                                @if($word['orientation'] === 'horizontal')
                                                    <li class="list-group-item">
                                                        <strong>{{ $word['index'] }}.</strong> {{ $word['clue'] }}
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>

                                    <div class="col-md-6">
                                        <h5>Down</h5>
                                        <ul class="list-group">
                                            @foreach($crossword->words as $word)
                                                @if($word['orientation'] === 'vertical')
                                                    <li class="list-group-item">
                                                        <strong>{{ $word['index'] }}.</strong> {{ $word['clue'] }}
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.crossword-grid {
    display: inline-block;
    border: 2px solid #333;
    background-color: #000;
}

.crossword-row {
    display: flex;
}

.crossword-cell {
    width: 25px;
    height: 25px;
    position: relative;
    background-color: #000;
    border: 1px solid #555;
}

.crossword-cell.has-letter {
    background-color: #fff;
}

.word-index {
    position: absolute;
    top: 1px;
    left: 1px;
    font-size: 8px;
    color: #333;
}

.letter {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 12px;
    font-weight: bold;
}
</style>
@endsection
