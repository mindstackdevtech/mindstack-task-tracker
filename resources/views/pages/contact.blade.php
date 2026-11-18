@extends('layouts.public')

@section('title', 'Contact us')

@section('content')
    <header class="public-hero">
        <p class="eyebrow">Contact us</p>
        <h1>Send us a message</h1>
        <p class="lead">Questions, problems or ideas for the app. We reply by email.</p>
    </header>

    <section class="panel narrow-center">
        <form method="POST" action="{{ route('contact.send') }}" class="form">
            @csrf

            {{-- Hidden from people. Programs that fill in every field get ignored. --}}
            <div class="hp" aria-hidden="true">
                <label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <div class="field-row">
                <label class="field">
                    <span>Your name</span>
                    <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}" required maxlength="100" autocomplete="name">
                    @error('name') <small class="error">{{ $message }}</small> @enderror
                </label>

                <label class="field">
                    <span>Your email</span>
                    <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required maxlength="255" autocomplete="email">
                    @error('email') <small class="error">{{ $message }}</small> @enderror
                </label>
            </div>

            <label class="field">
                <span>Subject</span>
                <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="150">
                @error('subject') <small class="error">{{ $message }}</small> @enderror
            </label>

            <label class="field">
                <span>Message</span>
                <textarea name="message" rows="6" required maxlength="3000">{{ old('message') }}</textarea>
                @error('message') <small class="error">{{ $message }}</small> @enderror
            </label>

            <div><button type="submit" class="btn btn-primary">Send message</button></div>
        </form>
    </section>

    <section class="panel narrow-center">
        <h2>Other ways to reach us</h2>
        @include('partials.developer-links')
    </section>
@endsection
