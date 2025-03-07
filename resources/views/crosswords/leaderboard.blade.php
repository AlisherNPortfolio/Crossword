<!-- resources/views/crosswords/leaderboard.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Leaderboard</h2>
                </div>

                <div class="card-body">
                    <h3 class="mb-4">Top Solvers</h3>

                    @if($topUsers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User</th>
                                        <th>Crosswords Solved</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topUsers as $index => $userData)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                {{ $userData->user->name }}
                                                @if(Auth::id() === $userData->user_id)
                                                    <span class="badge bg-success">You</span>
                                                @endif
                                            </td>
                                            <td>{{ $userData->solved_count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center">
                            <p class="text-muted">No users have completed any crosswords yet.</p>
                        </div>
                    @endif

                    <hr />

                    <div class="mt-4 text-center">
                        <h4>Want to climb the leaderboard?</h4>
                        <p>Solve more crosswords to improve your ranking!</p>
                        <a href="{{ route('crosswords.index') }}" class="btn btn-primary">Browse Crosswords</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
