<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - Dashboard | {{ config('app.name', 'Crossword App') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/dashboard.css') }}" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar bg-dark">
            <div class="sidebar-header">
                <a href="{{ route('dashboard.index') }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                    <span class="fs-4">{{ config('app.name', 'Crossword App') }}</span>
                </a>
            </div>

            <hr class="text-white">

            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="{{ route('dashboard.index') }}" class="nav-link {{ request()->routeIs('dashboard.index') ? 'active' : 'text-white' }}">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('dashboard.crosswords.index') }}" class="nav-link {{ request()->routeIs('dashboard.crosswords.*') ? 'active' : 'text-white' }}">
                        <i class="bi bi-grid-3x3-gap me-2"></i>
                        Crosswords
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('dashboard.competitions.index') }}" class="nav-link {{ request()->routeIs('dashboard.competitions.*') ? 'active' : 'text-white' }}">
                        <i class="bi bi-trophy me-2"></i>
                        Competitions
                    </a>
                </li>

                @if(auth()->user()->isAdmin())
                    <li class="nav-item">
                        <a href="{{ route('dashboard.users.index') }}" class="nav-link {{ request()->routeIs('dashboard.users.*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-people me-2"></i>
                            Users
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('dashboard.roles.index') }}" class="nav-link {{ request()->routeIs('dashboard.roles.*') ? 'active' : 'text-white' }}">
                            <i class="bi bi-person-badge me-2"></i>
                            Roles
                        </a>
                    </li>
                @endif
            </ul>

            <hr class="text-white">

            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ Storage::url(auth()->user()->profile_photo) }}" alt="{{ auth()->user()->name }}" width="32" height="32" class="rounded-circle me-2">
                    @else
                        <i class="bi bi-person-circle me-2" style="font-size: 1.5rem;"></i>
                    @endif
                    <strong>{{ auth()->user()->name }}</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                    <li><a class="dropdown-item" href="{{ route('profile.show') }}">Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('home') }}">Back to Site</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">Sign out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <div class="main-content">
            <header class="p-3 mb-3 border-bottom">
                <div class="container">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <h1 class="h2">@yield('title')</h1>
                        <div class="d-flex align-items-center">
                            @yield('actions')
                        </div>
                    </div>
                </div>
            </header>

            <main class="container py-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
