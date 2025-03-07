<!-- resources/views/crosswords/create.blade.php -->
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

            <crossword-creator></crossword-creator>
        </div>
    </div>
</div>
@endsection
