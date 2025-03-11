@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">My Competition History</h4>
                    <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">Back to Profile</a>
                </div>
                <div class="card-body">
                    @if($competitionResults->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Competition</th>
                                        <th>Crossword</th>
                                        <th>Status</th>
                                        <th>Ranking</th>
                                        <th>Score</th>
                                        <th>Time Taken</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($competitionResults as $result)
                                        <tr>
                                            <td>{{ $result->competition->title }}</td>
                                            <td>{{ $result->competition->crossword->title }}</td>
                                            <td>
                                                @if($result->completed)
                                                    <span class="badge bg-success">Completed</span>
                                                @else
                                                    <span class="badge bg-warning">In Progress</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($result->ranking)
                                                    @if($result->ranking == 1)
                                                        <span class="badge bg-warning"><i class="bi bi-trophy"></i> {{ $result->ranking }}</span>
                                                    @else
                                                        {{ $result->ranking }}
                                                    @endif
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ $result->score }}</td>
                                            <td>
                                                @if($result->time_taken)
                                                    {{ floor($result->time_taken / 60) }}:{{ str_pad($result->time_taken % 60, 2, '0', STR_PAD_LEFT) }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ $result->updated_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('competitions.show', $result->competition) }}" class="btn btn-sm btn-info">
                                                    Details
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $competitionResults->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="mb-3">You haven't participated in any competitions yet.</p>
                            <a href="{{ route('competitions.index') }}" class="btn btn-primary">Browse Competitions</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
