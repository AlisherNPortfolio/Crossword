@extends('layouts.dashboard')

@section('title', 'Create Competition')

@section('actions')
    <a href="{{ route('dashboard.competitions.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Competitions
    </a>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-card">
                <form method="POST" action="{{ route('dashboard.competitions.store') }}">
                    @csrf

                    <div class="form-section">
                        <h3 class="form-section-title">Competition Information</h3>

                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="crossword_id" class="form-label">Select Crossword</label>
                            <select class="form-select @error('crossword_id') is-invalid @enderror" id="crossword_id" name="crossword_id" required>
                                <option value="">-- Select a crossword --</option>
                                @foreach($crosswords as $crossword)
                                    <option value="{{ $crossword->id }}" {{ old('crossword_id') == $crossword->id ? 'selected' : '' }}>
                                        {{ $crossword->title }}
                                        @if(auth()->user()->isAdmin() && $crossword->created_by !== auth()->id())
                                            (by {{ $crossword->creator->name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('crossword_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Only published crosswords can be used for competitions.</div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Schedule</h3>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time</label>
                                    <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time</label>
                                    <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('dashboard.competitions.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Competition</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
