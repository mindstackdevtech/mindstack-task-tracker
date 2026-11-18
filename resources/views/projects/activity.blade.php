@extends('layouts.app')

@section('title', 'Activity · '.$project->name)

@section('content')
    <header class="page-head">
        <div>
            <p class="muted small">
                <a href="{{ route('projects.index') }}">Projects</a> /
                <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a> /
            </p>
            <h1>Activity history</h1>
            <p class="muted">{{ $activities->total() }} {{ Str::plural('entry', $activities->total()) }}</p>
        </div>
        <a href="{{ route('projects.show', $project) }}" class="btn">Back to board</a>
    </header>

    <section class="panel">
        @forelse ($activities as $activity)
            <div class="activity">
                @include('partials.avatar', ['person' => $activity->user, 'large' => true])
                <div>
                    <strong>{{ $activity->user_id === auth()->id() ? 'You' : ($activity->user?->name ?? 'Former member') }}</strong>
                    @if ($activity->task_id)
                        <a href="{{ route('tasks.show', $activity->task_id) }}">{{ $activity->description }}</a>
                    @else
                        {{ $activity->description }}
                    @endif
                    <div class="muted small">{{ $activity->created_at->format('M j, Y · g:i A') }}</div>
                </div>
            </div>
        @empty
            <p class="empty">Nothing has happened in this project yet.</p>
        @endforelse
    </section>

    @if ($activities->hasPages())
        <nav class="pager" aria-label="Pages">
            @if ($activities->onFirstPage())
                <span class="muted">← Newer</span>
            @else
                <a href="{{ $activities->previousPageUrl() }}">← Newer</a>
            @endif

            <span class="muted small">Page {{ $activities->currentPage() }} of {{ $activities->lastPage() }}</span>

            @if ($activities->hasMorePages())
                <a href="{{ $activities->nextPageUrl() }}">Older →</a>
            @else
                <span class="muted">Older →</span>
            @endif
        </nav>
    @endif
@endsection
