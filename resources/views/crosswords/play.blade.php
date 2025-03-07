<!-- resources/views/crosswords/play.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="mb-3">
                <a href="{{ route('crosswords.show', $crossword) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Crossword Details
                </a>
            </div>

            <crossword-player
                :crossword-id="{{ $crossword->id }}"
                @if($userSolution)
                :saved-solution="{{ json_encode($userSolution) }}"
                @endif
            ></crossword-player>
        </div>
    </div>
</div>
@endsection
