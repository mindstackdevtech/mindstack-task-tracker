@extends('layouts.app')

@section('title', 'Log in')

@section('content')
    <h1>Log in</h1>

    <form method="POST" action="{{ route('login') }}" class="form">
        @csrf

        <label class="field">
            <span>Email</span>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            @error('email') <small class="error">{{ $message }}</small> @enderror
        </label>

        <label class="field">
            <span>Password</span>
            <input type="password" name="password" required autocomplete="current-password">
            @error('password') <small class="error">{{ $message }}</small> @enderror
        </label>

        <div class="auth-row">
            <label class="check">
                <input type="checkbox" name="remember" value="1">
                <span>Keep me logged in</span>
            </label>
            <a href="{{ route('password.request') }}">Forgot your password?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Log in</button>
    </form>

    <p class="muted center">No account yet? <a href="{{ route('register') }}">Create one</a></p>
@endsection
