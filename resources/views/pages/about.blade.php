@extends('layouts.public')

@section('title', 'About us')

{{-- The developer's name and links come from config/mindstack.php.
     Edit the rest of the text on this page to say what you want. --}}

@php
    $dev = config('mindstack.developer', []);
    $devName = $dev['name'] ?? 'MindStack Dev & Tech';
    $nameParts = array_values(array_filter(explode(' ', trim($devName))));
    $devInitials = mb_strtoupper(mb_substr($nameParts[0] ?? '?', 0, 1));
    if (count($nameParts) > 1) {
        $devInitials .= mb_strtoupper(mb_substr($nameParts[count($nameParts) - 1], 0, 1));
    }
@endphp

@section('content')
    <header class="public-hero">
        <p class="eyebrow">About us</p>
        <h1>A simpler way to run your team's work</h1>
        <p class="lead">
            MindStack Task Tracker keeps a team's projects, tasks and conversations in one place,
            so everyone knows what to do next and who is doing it.
        </p>
    </header>

    <section class="panel">
        <h2>What you can do with it</h2>
        <div class="feature-grid">
            <div>
                <h3>Plan on a board</h3>
                <p class="muted">Move tasks from To Do to In Progress, In Review and Done.</p>
            </div>
            <div>
                <h3>Work as a team</h3>
                <p class="muted">Invite people to a project and assign tasks to each other.</p>
            </div>
            <div>
                <h3>Stay informed</h3>
                <p class="muted">Get notified when you are given a task or someone comments.</p>
            </div>
            <div>
                <h3>Talk it through</h3>
                <p class="muted">Each project has its own team chat, checklists and comments.</p>
            </div>
            <div>
                <h3>See your deadlines</h3>
                <p class="muted">A dashboard and a calendar show what is due and what is overdue.</p>
            </div>
            <div>
                <h3>Use it anywhere</h3>
                <p class="muted">It works on phones, tablets and computers.</p>
            </div>
        </div>
    </section>

    <section class="panel">
        <h2>The developer behind it</h2>

        <div class="dev-card">
            <span class="team-avatar avatar-1" aria-hidden="true">{{ $devInitials }}</span>
            <div class="dev-info">
                <h3>{{ $devName }}</h3>
                <p class="team-role">{{ $dev['role'] ?? 'Developer' }} · MindStack Dev &amp; Tech</p>
                @if (! empty($dev['about']))
                    <p class="muted">{{ $dev['about'] }}</p>
                @endif

                @include('partials.developer-links')
            </div>
        </div>
    </section>

    <section class="panel">
        <h2>Get in touch</h2>
        <p class="muted">Have a question or an idea for the app? Send a message and you will get a reply by email.</p>
        <p><a href="{{ route('contact') }}" class="btn btn-primary">Contact us</a></p>
    </section>
@endsection
