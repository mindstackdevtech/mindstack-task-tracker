@extends('layouts.app')

@section('title', 'Create account')

@section('content')
    <h1>Create account</h1>

    <form method="POST" action="{{ route('register') }}" class="form">
        @csrf

        <label class="field">
            <span>Name</span>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            @error('name') <small class="error">{{ $message }}</small> @enderror
        </label>

        <label class="field">
            <span>Email</span>
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            @error('email') <small class="error">{{ $message }}</small> @enderror
        </label>

        <label class="field">
            <span>Password</span>
            <input type="password" name="password" required autocomplete="new-password">
            <small class="muted">At least 8 characters.</small>
            @error('password') <small class="error">{{ $message }}</small> @enderror
        </label>

        <label class="field">
            <span>Confirm password</span>
            <input type="password" name="password_confirmation" required autocomplete="new-password">
        </label>

        <button type="submit" class="btn btn-primary btn-block">Create account</button>
    </form>

    <p class="muted center">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
@endsection
