@extends('layouts.app')

@section('title', 'Forgot password')

@section('content')
    <h1>Forgot your password?</h1>
    <p class="muted center">Enter your email and we will send you a link to choose a new password.</p>

    <form method="POST" action="{{ route('password.email') }}" class="form">
        @csrf

        <label class="field">
            <span>Email</span>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            @error('email') <small class="error">{{ $message }}</small> @enderror
        </label>

        <button type="submit" class="btn btn-primary btn-block">Email me a reset link</button>
    </form>

    <p class="muted center"><a href="{{ route('login') }}">Back to log in</a></p>
@endsection
