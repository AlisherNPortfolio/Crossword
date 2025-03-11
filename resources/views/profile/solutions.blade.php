@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">My Solved Crosswords</h4>
                    <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">Back to Profile</a>
                </div>
                <div class="card-body">
                    @if($solutions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Crossword</th>
                                        <th>Creator</th>
                                        <th>Status</th>
                                        <th>Score</th>
                                        <th>Time Taken</th>
                                        <th>Last Played</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($solutions as $solution)
                                        <tr>
                                            <td>{{ $solution->crossword->title }}</td>
                                            <td>{{ $solution->crossword->creator->name }}</td>
                                            <td>
                                                @if($solution->completed)
                                                    <span class="badge bg-success">Completed</span>
                                                @else
                                                    <span class="badge bg-warning">In Progress</span>
                                                @endif
                                            </td>
                                            <td>{{ $solution->score }}</td>
                                            <td>
                                                @if($solution->time_taken)
                                                    {{ floor($solution->time_taken / 60) }}:{{ str_pad($solution->time_taken % 60, 2, '0', STR_PAD_LEFT) }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ $solution->updated_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('crosswords.play', $solution->crossword) }}" class="btn btn-sm btn-primary">
                                                    {{ $solution->completed ? 'Play Again' : 'Continue' }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $solutions->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="mb-3">You haven't solved any crosswords yet.</p>
                            <a href="{{ route('crosswords.index') }}" class="btn btn-primary">Browse Crosswords</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
