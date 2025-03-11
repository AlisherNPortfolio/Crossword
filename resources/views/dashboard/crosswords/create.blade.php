@extends('layouts.dashboard')

@section('title', 'Create Crossword')

@section('actions')
    <a href="{{ route('dashboard.crosswords.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Crosswords
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="form-card">
                <crossword-creator></crossword-creator>
            </div>
        </div>
    </div>
@endsection
