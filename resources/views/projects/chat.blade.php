@extends('layouts.app')

@section('title', 'Team chat · '.$project->name)

@section('content')
    <header class="page-head">
        <div>
            <p class="muted small">
                <a href="{{ route('projects.index') }}">Projects</a> /
                <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a> /
            </p>
            <h1>Team chat</h1>
            <p class="muted">Only members of this project can read and write here.</p>
        </div>
        <a href="{{ route('projects.show', $project) }}" class="btn">Back to board</a>
    </header>

    <section class="panel chat">
        <div class="chat-list" id="chat-list"
             data-feed="{{ route('projects.chat.feed', $project) }}"
             data-last="{{ $lastId }}"
             aria-live="polite">
            @foreach ($messages as $item)
                <div @class(['msg', 'mine' => $item['mine'], 'mentions-me' => $item['mentionsMe']])>
                    <span class="avatar avatar-lg avatar-{{ $item['shade'] }}" aria-hidden="true">{{ $item['initials'] }}</span>
                    <div class="msg-bubble">
                        <div class="msg-head">
                            <strong>{{ $item['mine'] ? 'You' : $item['name'] }}</strong>
                            <span class="muted small">{{ $item['time'] }}</span>
                        </div>
                        {{-- Already escaped in ChatController::highlight(); only the mention highlights are HTML. --}}
                        <div class="msg-body">{!! $item['html'] !!}</div>
                    </div>
                </div>
            @endforeach

            <p class="empty" id="chat-empty" @if ($messages->isNotEmpty()) hidden @endif>
                No messages yet. Say hello to your team.
            </p>
        </div>

        <form method="POST" action="{{ route('projects.chat.store', $project) }}" class="chat-form" id="chat-form">
            @csrf
            <div class="chat-input-wrap">
                {{-- The list that opens when you type "@" (filled in by chat.js). --}}
                <ul class="mention-list" id="mention-list" role="listbox" aria-label="Mention a member" hidden></ul>

                <input type="text" name="body" id="chat-input" placeholder="Write a message. Type @ to mention someone"
                       aria-label="Write a message" required maxlength="2000" autocomplete="off" autofocus
                       data-members='@json($mentionNames)'>
            </div>
            <button type="submit" class="btn btn-primary">Send</button>
        </form>
        @error('body') <small class="error">{{ $message }}</small> @enderror

        <p class="muted small chat-hint">
            Type <strong>@</strong> and pick a name to mention someone, or <strong>@everyone</strong> for the whole team.
            They get a notification and an email.
        </p>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/chat.js') }}" defer></script>
@endpush
