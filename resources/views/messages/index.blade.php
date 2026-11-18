@extends('layouts.app')

@section('title', 'Messages')

@section('content')
    <header class="page-head">
        <div>
            <h1>Messages</h1>
            <p class="muted">Private conversations with people in your projects. Only the two of you can read them.</p>
        </div>
    </header>

    <section class="panel thread-list">
        @forelse ($people as $row)
            <a href="{{ route('messages.show', $row['user']) }}" @class(['thread', 'unread' => $row['unread'] > 0])>
                @include('partials.avatar', ['person' => $row['user'], 'large' => true])

                <div class="thread-text">
                    <div class="row-line">
                        <span class="row-title">{{ $row['user']->name }}</span>
                        @if ($row['last'])
                            <span class="muted small">{{ $row['last']->created_at->diffForHumans(null, true) }}</span>
                        @endif
                    </div>

                    <div class="muted thread-preview">
                        @if ($row['last'])
                            @if ($row['last']->sender_id === $me->id) You: @endif{{ Str::limit($row['last']->body, 80) }}
                        @else
                            Start a conversation
                        @endif
                    </div>
                </div>

                @if ($row['unread'] > 0)
                    <b class="thread-badge" aria-label="{{ $row['unread'] }} unread">{{ $row['unread'] > 99 ? '99+' : $row['unread'] }}</b>
                @endif
            </a>
        @empty
            <p class="empty">
                Nobody to message yet. Invite people to one of your
                <a href="{{ route('projects.index') }}">projects</a> and they will appear here.
            </p>
        @endforelse
    </section>
@endsection
