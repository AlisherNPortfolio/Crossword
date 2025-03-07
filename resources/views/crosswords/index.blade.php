<!-- resources/views/crosswords/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>All Crosswords</h2>
                    <a href="{{ route('crosswords.create') }}" class="btn btn-primary">Create New</a>
                </div>

                <div class="card-body">
                    @if($crosswords->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Creator</th>
                                        <th>Words</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($crosswords as $crossword)
                                        <tr>
                                            <td>{{ $crossword->title }}</td>
                                            <td>{{ $crossword->creator->name }}</td>
                                            <td>{{ count($crossword->words) }}</td>
                                            <td>{{ $crossword->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <a href="{{ route('crosswords.show', $crossword) }}" class="btn btn-sm btn-info">View</a>
                                                <a href="{{ route('crosswords.play', $crossword) }}" class="btn btn-sm btn-success">Play</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $crosswords->links() }}
                        </div>
                    @else
                        <div class="text-center">
                            <p class="text-muted">No crosswords available yet.</p>
                            <a href="{{ route('crosswords.create') }}" class="btn btn-primary">Create First Crossword</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
