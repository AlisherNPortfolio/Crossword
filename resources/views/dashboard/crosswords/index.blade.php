@extends('layouts.dashboard')

@section('title', 'Crossword Management')

@section('actions')
    <a href="{{ route('dashboard.crosswords.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Create Crossword
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <form action="{{ route('dashboard.crosswords.index') }}" method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Search by title..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-outline-primary">Search</button>
                    </form>
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Filter by Status
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                            <li><a class="dropdown-item {{ request('status') == '' ? 'active' : '' }}" href="{{ route('dashboard.crosswords.index') }}">All</a></li>
                            <li><a class="dropdown-item {{ request('status') == 'published' ? 'active' : '' }}" href="{{ route('dashboard.crosswords.index', ['status' => 'published']) }}">Published</a></li>
                            <li><a class="dropdown-item {{ request('status') == 'draft' ? 'active' : '' }}" href="{{ route('dashboard.crosswords.index', ['status' => 'draft']) }}">Draft</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Creator</th>
                            <th>Words</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Solutions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($crosswords as $crossword)
                            <tr>
                                <td>{{ $crossword->id }}</td>
                                <td>{{ $crossword->title }}</td>
                                <td>{{ $crossword->creator->name }}</td>
                                <td>{{ count($crossword->words) }}</td>
                                <td>
                                    @if($crossword->published)
                                        <span class="badge bg-success">Published</span>
                                    @else
                                        <span class="badge bg-secondary">Draft</span>
                                    @endif
                                </td>
                                <td>{{ $crossword->created_at->format('M d, Y') }}</td>
                                <td>{{ $crossword->solutions_count ?? 0 }}</td>
                                <td class="action-buttons">
                                    <a href="{{ route('dashboard.crosswords.show', $crossword) }}"
                                       class="btn btn-sm btn-info"
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @if(auth()->user()->isAdmin() || auth()->id() === $crossword->created_by)
                                        @if($crossword->solutions_count == 0)
                                            <a href="{{ route('dashboard.crosswords.edit', $crossword) }}"
                                               class="btn btn-sm btn-primary"
                                               title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif

                                        <form action="{{ route('dashboard.crosswords.toggle-publish', $crossword) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                   class="btn btn-sm {{ $crossword->published ? 'btn-warning' : 'btn-success' }}"
                                                   title="{{ $crossword->published ? 'Unpublish' : 'Publish' }}">
                                                <i class="bi {{ $crossword->published ? 'bi-eye-slash' : 'bi-eye' }}"></i>
                                            </button>
                                        </form>

                                        @if($crossword->solutions_count == 0 && $crossword->competitions_count == 0)
                                            <form action="{{ route('dashboard.crosswords.destroy', $crossword) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                      class="btn btn-sm btn-danger"
                                                      title="Delete"
                                                      onclick="return confirm('Are you sure you want to delete this crossword?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No crosswords found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $crosswords->links() }}
            </div>
        </div>
    </div>
@endsection
