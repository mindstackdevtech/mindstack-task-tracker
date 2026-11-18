@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <header class="page-head">
        <div>
            <h1>Notifications</h1>
            <p class="muted">{{ $unread }} unread</p>
        </div>

        @if ($unread > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn">Mark all as read</button>
            </form>
        @endif
    </header>

    <section class="panel notif-list">
        @forelse ($notifications as $notification)
            <a href="{{ route('notifications.open', $notification->id) }}" @class(['notif', 'unread' => $notification->read_at === null])>
                <span class="notif-dot" aria-hidden="true"></span>
                <div class="notif-text">
                    <div class="row-title">
                        {{ $notification->data['title'] ?? 'Notification' }}
                        @if ($notification->read_at === null)
                            <span class="pill pill-high">New</span>
                        @endif
                    </div>
                    <div class="muted">{{ $notification->data['body'] ?? '' }}</div>
                    <div class="muted small">{{ $notification->created_at->diffForHumans() }}</div>
                </div>
            </a>
        @empty
            <p class="empty">You have no notifications yet. You will get one when someone assigns you a task, comments on your task, or adds you to a project.</p>
        @endforelse
    </section>

    @if ($notifications->hasPages())
        <nav class="pager" aria-label="Pages">
            @if ($notifications->onFirstPage())
                <span class="muted">← Newer</span>
            @else
                <a href="{{ $notifications->previousPageUrl() }}">← Newer</a>
            @endif

            <span class="muted small">Page {{ $notifications->currentPage() }} of {{ $notifications->lastPage() }}</span>

            @if ($notifications->hasMorePages())
                <a href="{{ $notifications->nextPageUrl() }}">Older →</a>
            @else
                <span class="muted">Older →</span>
            @endif
        </nav>
    @endif
@endsection
