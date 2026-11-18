{{-- Top bar for pages people can see without logging in.
     Pass 'brand' => true to show the logo on the left. --}}
<header class="public-bar">
    @if ($brand ?? true)
        <a href="{{ url('/') }}" class="public-brand">
            <img src="{{ asset('images/icon.png') }}" alt="" aria-hidden="true">
            <span>MindStack <em>Task Tracker</em></span>
        </a>
    @else
        <span></span>
    @endif

    <nav class="public-links" aria-label="Site">
        <a href="{{ route('about') }}" @class(['active' => request()->routeIs('about')])>About us</a>
        <a href="{{ route('contact') }}" @class(['active' => request()->routeIs('contact')])>Contact us</a>

        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">Dashboard</a>
        @else
            @unless (request()->routeIs('login'))
                <a href="{{ route('login') }}" class="btn btn-sm">Log in</a>
            @endunless
            @unless (request()->routeIs('register'))
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Create account</a>
            @endunless
        @endauth
    </nav>
</header>
