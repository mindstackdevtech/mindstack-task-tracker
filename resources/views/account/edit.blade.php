@extends('layouts.app')

@section('title', 'Account')

@section('content')
    <header class="page-head">
        <div>
            <h1>Account</h1>
            <p class="muted">Member since {{ $user->created_at->format('F j, Y') }}</p>
        </div>
    </header>

    <section class="panel narrow">
        <div class="panel-head">
            <h2>Your details</h2>
        </div>

        <form method="POST" action="{{ route('account.profile') }}" class="form">
            @csrf
            @method('PUT')

            <label class="field">
                <span>Name</span>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name">
                @error('name') <small class="error">{{ $message }}</small> @enderror
            </label>

            <label class="field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email">
                <small class="muted">Notifications and password reset links are sent here.</small>
                @error('email') <small class="error">{{ $message }}</small> @enderror
            </label>

            <label class="field">
                <span>Your password <em class="muted">(only needed when changing your email)</em></span>
                <input type="password" name="current_password" autocomplete="current-password">
                @error('current_password') <small class="error">{{ $message }}</small> @enderror
            </label>

            <div><button type="submit" class="btn btn-primary">Save details</button></div>
        </form>
    </section>

    <section class="panel narrow">
        <div class="panel-head">
            <h2>Change password</h2>
        </div>

        <form method="POST" action="{{ route('account.password') }}" class="form">
            @csrf
            @method('PUT')

            <label class="field">
                <span>Current password</span>
                <input type="password" name="current_password" required autocomplete="current-password">
                @error('current_password') <small class="error">{{ $message }}</small> @enderror
            </label>

            <label class="field">
                <span>New password</span>
                <input type="password" name="password" required autocomplete="new-password">
                <small class="muted">{{ \App\Support\Security::PASSWORD_HINT }}</small>
                @error('password') <small class="error">{{ $message }}</small> @enderror
            </label>

            <label class="field">
                <span>Confirm new password</span>
                <input type="password" name="password_confirmation" required autocomplete="new-password">
            </label>

            <div><button type="submit" class="btn btn-primary">Change password</button></div>
        </form>
    </section>
@endsection
