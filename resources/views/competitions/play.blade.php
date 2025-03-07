<!-- resources/views/competitions/play.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="mb-3">
                <a href="{{ route('competitions.show', $competition) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Competition Details
                </a>
            </div>

            <competition-player
                :competition="{{ json_encode([
                    'id' => $competition->id,
                    'title' => $competition->title,
                    'description' => $competition->description,
                    'crossword' => [
                        'id' => $competition->crossword->id,
                        'title' => $competition->crossword->title
                    ],
                    'start_time' => $competition->start_time,
                    'end_time' => $competition->end_time
                ]) }}"
                @if(isset($userResult))
                :saved-solution="{{ json_encode($userResult) }}"
                @endif
            ></competition-player>
        </div>
    </div>
</div>
@endsection
