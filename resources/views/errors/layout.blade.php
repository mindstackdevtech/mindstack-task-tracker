<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b0d0c">
    <title>@yield('title') · MindStack Task Tracker</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/extras.css') }}">
    <link rel="stylesheet" href="{{ asset('css/polish.css') }}">
</head>
<body>
    <main class="auth-wrap">
        <div class="auth-card error-card">
            <img class="auth-logo" src="{{ asset('images/logo.png') }}" alt="MindStack Task Tracker">
            <p class="error-code">@yield('code')</p>
            <h1>@yield('title')</h1>
            <p class="muted center">@yield('message')</p>
            <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-block">Go to the dashboard</a>
        </div>
    </main>
</body>
</html>
