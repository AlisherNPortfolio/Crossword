@extends('layouts.dashboard')

@section('title', 'Edit Crossword')

@section('actions')
    <a href="{{ route('dashboard.crosswords.show', $crossword) }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Crossword
    </a>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-card">
                <form method="POST" action="{{ route('dashboard.crosswords.update', $crossword) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-section">
                        <h3 class="form-section-title">Basic Information</h3>

                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $crossword->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="publish" name="publish" {{ $crossword->published ? 'checked' : '' }}>
                            <label class="form-check-label" for="publish">Publish Crossword</label>
                            @if(!auth()->user()->hasPermission('crosswords.publish'))
                                <div class="form-text text-danger">You don't have permission to publish crosswords. Please contact an administrator.</div>
                            @endif
                        </div>

                        <div class="alert alert-info">
                            <p class="mb-0"><i class="bi bi-info-circle me-2"></i> Word list and crossword grid cannot be edited once created. This is to ensure the integrity of any existing solutions.</p>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Preview</h3>

                        <div class="crossword-grid-container text-center">
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

                        <div class="row mt-4">
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

                    <div class="form-section">
                        <h3 class="form-section-title">Crossword Data</h3>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Number of Words</label>
                                    <p class="form-control-static">{{ count($crossword->words) }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Grid Size</label>
                                    <p class="form-control-static">{{ count($crossword->grid_data) }} x {{ count($crossword->grid_data[0]) }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Creator</label>
                                    <p class="form-control-static">{{ $crossword->creator->name }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Created On</label>
                                    <p class="form-control-static">{{ $crossword->created_at->format('F j, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('dashboard.crosswords.show', $crossword) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Crossword</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
