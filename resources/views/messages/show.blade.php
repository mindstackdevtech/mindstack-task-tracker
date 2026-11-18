@extends('layouts.app')

@section('title', $other->name.' · Messages')

@section('content')
    <header class="page-head">
        <div>
            <p class="muted small"><a href="{{ route('messages.index') }}">Messages</a> /</p>
            <h1>{{ $other->name }}</h1>
            <p class="muted">Private. Only you and {{ Str::before($other->name, ' ') }} can read this.</p>
        </div>
        <a href="{{ route('messages.index') }}" class="btn">All messages</a>
    </header>

    <section class="panel chat">
        {{-- Same ids as the team chat, so public/js/chat.js runs this page too. --}}
        <div class="chat-list" id="chat-list"
             data-feed="{{ route('messages.feed', $other) }}"
             data-last="{{ $lastId }}"
             aria-live="polite">
            @foreach ($messages as $item)
                <div @class(['msg', 'mine' => $item['mine']])>
                    <span class="avatar avatar-lg avatar-{{ $item['shade'] }}" aria-hidden="true">{{ $item['initials'] }}</span>
                    <div class="msg-bubble">
                        <div class="msg-head">
                            <strong>{{ $item['mine'] ? 'You' : $item['name'] }}</strong>
                            <span class="muted small">{{ $item['time'] }}</span>
                        </div>
                        <div class="msg-body">{{ $item['body'] }}</div>
                    </div>
                </div>
            @endforeach

            <p class="empty" id="chat-empty" @if ($messages->isNotEmpty()) hidden @endif>
                No messages yet. Say hello to {{ Str::before($other->name, ' ') }}.
            </p>
        </div>

        @if ($canWrite)
            <form method="POST" action="{{ route('messages.store', $other) }}" class="chat-form" id="chat-form">
                @csrf
                <div class="chat-input-wrap">
                    <input type="text" name="body" id="chat-input" placeholder="Write a message"
                           aria-label="Write a message to {{ $other->name }}" required maxlength="2000" autocomplete="off" autofocus>
                </div>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
            @error('body') <small class="error">{{ $message }}</small> @enderror
        @else
            <p class="muted small chat-hint">
                You no longer share a project with {{ Str::before($other->name, ' ') }}, so you can read this conversation but not add to it.
            </p>
        @endif
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/chat.js') }}" defer></script>
@endpush
