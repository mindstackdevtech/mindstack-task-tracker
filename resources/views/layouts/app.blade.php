<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b0d0c">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · MindStack Task Tracker</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/extras.css') }}">
</head>
<body>
@auth
    @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp

    <div class="shell">
        <aside class="sidebar">
            <a href="{{ route('dashboard') }}" class="brand">
                <img class="brand-full" src="{{ asset('images/logo.png') }}" alt="MindStack Task Tracker">
                <img class="brand-icon" src="{{ asset('images/icon.png') }}" alt="" aria-hidden="true">
                <span class="brand-name">MindStack</span>
            </a>

            {{-- Side menu on desktop, bottom tab bar on phones (see app.css). --}}
            <nav class="nav" aria-label="Main">
                <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M4 11l8-7 8 7v9h-5v-6H9v6H4z"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('projects.index') }}" @class(['active' => request()->routeIs('projects.*')])>
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M4 5h5v14H4zM10 5h5v9h-5zM16 5h4v6h-4z"/></svg>
                    <span>Projects</span>
                </a>
                <a href="{{ route('tasks.index') }}" @class(['active' => request()->routeIs('tasks.*')])>
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6h11M9 12h11M9 18h11M4 6l1 1 2-2M4 12l1 1 2-2M4 18l1 1 2-2"/></svg>
                    <span>All tasks</span>
                </a>
                <a href="{{ route('calendar.index') }}" @class(['active' => request()->routeIs('calendar.*')])>
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16v14H4zM4 10h16M8 3v4M16 3v4"/></svg>
                    <span>Calendar</span>
                </a>
                <a href="{{ route('notifications.index') }}" @class(['nav-bell', 'active' => request()->routeIs('notifications.*')])>
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 16V11a6 6 0 0 1 12 0v5l2 2H4zM10 21h4"/></svg>
                    <span class="nav-long">Notifications</span>
                    <span class="nav-short">Alerts</span>
                    @if ($unreadCount > 0)
                        <b class="nav-badge" aria-label="{{ $unreadCount }} unread">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</b>
                    @endif
                </a>
            </nav>

            <div class="sidebar-user">
                <div class="sidebar-user-info">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="muted small">{{ auth()->user()->email }}</div>
                </div>
                <div class="sidebar-user-actions">
                    <a href="{{ route('account.edit') }}" @class(['btn', 'btn-ghost', 'btn-sm', 'is-current' => request()->routeIs('account.*')])>Account</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm">Log out</button>
                    </form>
                </div>
                <div class="sidebar-links small">
                    <a href="{{ route('about') }}">About us</a>
                    <a href="{{ route('contact') }}">Contact us</a>
                </div>
            </div>
        </aside>

        <main class="main">
            @if (session('status'))
                <div class="flash" role="status">{{ session('status') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
@else
    <div class="public-page">
        @include('partials.public-nav', ['brand' => false])

        <main class="auth-wrap">
            <div class="auth-card">
                <img class="auth-logo" src="{{ asset('images/logo.png') }}" alt="MindStack Task Tracker">

                @if (session('status'))
                    <div class="flash" role="status">{{ session('status') }}</div>
                @endif

                @yield('content')
            </div>
        </main>

        @include('partials.public-footer')
    </div>
@endauth

@stack('scripts')
</body>
</html>
