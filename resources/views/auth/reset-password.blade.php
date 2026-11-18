@extends('layouts.app')

@section('title', 'Choose a new password')

@section('content')
    <h1>Choose a new password</h1>

    <form method="POST" action="{{ route('password.update') }}" class="form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <label class="field">
            <span>Email</span>
            <input type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email">
            @error('email') <small class="error">{{ $message }}</small> @enderror
        </label>

        <label class="field">
            <span>New password</span>
            <input type="password" name="password" required autofocus autocomplete="new-password">
            <small class="muted">{{ \App\Support\Security::PASSWORD_HINT }}</small>
            @error('password') <small class="error">{{ $message }}</small> @enderror
        </label>

        <label class="field">
            <span>Confirm new password</span>
            <input type="password" name="password_confirmation" required autocomplete="new-password">
        </label>

        <button type="submit" class="btn btn-primary btn-block">Save new password</button>
    </form>

    <p class="muted center"><a href="{{ route('login') }}">Back to log in</a></p>
@endsection
