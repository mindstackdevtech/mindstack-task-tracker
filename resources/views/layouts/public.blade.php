<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b0d0c">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · MindStack Task Tracker</title>
    <meta name="description" content="@yield('description', 'MindStack Task Tracker: a team task tracker by MindStack Dev & Tech.')">
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/extras.css') }}">
    <link rel="stylesheet" href="{{ asset('css/polish.css') }}">
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    {{-- Layout for About us and Contact us. Works logged in or logged out. --}}
    <div class="public-page">
        @include('partials.public-nav', ['brand' => true])

        <main class="public-main" id="main">
            @if (session('status'))
                <div class="flash" role="status">{{ session('status') }}</div>
            @endif

            @yield('content')
        </main>

        @include('partials.public-footer')
    </div>
    <script src="{{ asset('js/ui.js') }}" defer></script>
</body>
</html>
