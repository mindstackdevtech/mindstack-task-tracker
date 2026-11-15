@extends('layouts.app')

@section('title', $task->title)

@php
    $items = $task->checklistItems;
    $doneItems = $items->where('is_done', true)->count();
@endphp

@section('content')
    <header class="page-head">
        <div>
            <p class="muted small">
                <a href="{{ route('projects.index') }}">Projects</a> /
                <a href="{{ route('projects.show', $task->project) }}">{{ $task->project->name }}</a> /
            </p>
            <h1>{{ $task->title }}</h1>
            <div class="pill-row">
                <span class="pill">{{ $task->statusLabel() }}</span>
                <span class="pill pill-{{ $task->priority }}">{{ $task->priorityLabel() }}</span>
                @if ($task->due_date)
                    <span @class(['due', 'overdue' => $task->isOverdue()])>
                        @if ($task->isOverdue()) Overdue · @endif due {{ $task->due_date->format('M j') }}
                    </span>
                @endif
            </div>
        </div>

        <div class="head-actions">
            <form id="delete-form" method="POST" action="{{ route('tasks.destroy', $task) }}"
                  onsubmit="return confirm('Delete this task? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
            <button type="submit" form="task-form" class="btn btn-primary">Save changes</button>
        </div>
    </header>

    <div class="task-layout">
        <div class="stack">
            {{-- Fields in the side panel belong to this form through form="task-form". --}}
            <form id="task-form" method="POST" action="{{ route('tasks.update', $task) }}" class="panel form">
                @csrf
                @method('PUT')

                <label class="field">
                    <span>Title</span>
                    <input type="text" name="title" value="{{ old('title', $task->title) }}" required maxlength="255">
                    @error('title') <small class="error">{{ $message }}</small> @enderror
                </label>

                <label class="field">
                    <span>Description</span>
                    <textarea name="description" rows="5">{{ old('description', $task->description) }}</textarea>
                    @error('description') <small class="error">{{ $message }}</small> @enderror
                </label>
            </form>

            <section class="panel" id="checklist">
                <div class="panel-head">
                    <h2>Checklist</h2>
                    <span class="muted small">{{ $doneItems }} of {{ $items->count() }}</span>
                </div>

                @if ($items->isNotEmpty())
                    <div class="bar"><span style="width: {{ round($doneItems / $items->count() * 100) }}%"></span></div>
                @endif

                <div class="checklist">
                    @foreach ($items as $item)
                        <div class="check-row">
                            <form method="POST" action="{{ route('checklist.toggle', $item) }}">
                                @csrf
                                @method('PATCH')
                                <label @class(['check-item', 'checked' => $item->is_done])>
                                    <input type="checkbox" @checked($item->is_done) onchange="this.form.submit()">
                                    <span>{{ $item->title }}</span>
                                </label>
                            </form>
                            <form method="POST" action="{{ route('checklist.destroy', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn" aria-label="Remove {{ $item->title }}">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('checklist.store', $task) }}" class="inline-form">
                    @csrf
                    <input type="text" name="item_title" placeholder="Add an item" aria-label="New checklist item" required maxlength="255">
                    <button type="submit" class="btn">Add</button>
                </form>
                @error('item_title') <small class="error">{{ $message }}</small> @enderror
            </section>

            <section class="panel" id="comments">
                <div class="panel-head">
                    <h2>Comments</h2>
                    <span class="muted small">{{ $task->comments->count() }}</span>
                </div>

                @foreach ($task->comments as $comment)
                    <div class="comment">
                        @include('partials.avatar', ['person' => $comment->user, 'large' => true])
                        <div class="comment-body">
                            <div class="row-line">
                                <strong>{{ $comment->user_id === auth()->id() ? 'You' : ($comment->user?->name ?? 'Former member') }}</strong>
                                <span class="muted small">{{ $comment->created_at->format('M j, g:i A') }}</span>
                            </div>
                            <p>{!! nl2br(e($comment->body)) !!}</p>
                            @if ($comment->user_id === auth()->id())
                                <form method="POST" action="{{ route('comments.destroy', $comment) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-btn">Delete</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach

                <form method="POST" action="{{ route('comments.store', $task) }}" class="inline-form">
                    @csrf
                    <input type="text" name="body" placeholder="Write a comment" aria-label="Write a comment" required maxlength="2000">
                    <button type="submit" class="btn">Comment</button>
                </form>
                @error('body') <small class="error">{{ $message }}</small> @enderror
            </section>
        </div>

        <aside class="panel form" aria-label="Task details">
            <label class="field">
                <span>Status</span>
                <select name="status" form="task-form" required>
                    @foreach ($statuses as $value => $text)
                        <option value="{{ $value }}" @selected(old('status', $task->status) === $value)>{{ $text }}</option>
                    @endforeach
                </select>
                @error('status') <small class="error">{{ $message }}</small> @enderror
            </label>

            <label class="field">
                <span>Priority</span>
                <select name="priority" form="task-form" required>
                    @foreach ($priorities as $value => $text)
                        <option value="{{ $value }}" @selected(old('priority', $task->priority) === $value)>{{ $text }}</option>
                    @endforeach
                </select>
                @error('priority') <small class="error">{{ $message }}</small> @enderror
            </label>

            <label class="field">
                <span>Assignee</span>
                <select name="assigned_to" form="task-form">
                    <option value="">Unassigned</option>
                    @foreach ($users as $member)
                        <option value="{{ $member->id }}" @selected((string) old('assigned_to', $task->assigned_to) === (string) $member->id)>{{ $member->name }}</option>
                    @endforeach
                </select>
                @error('assigned_to') <small class="error">{{ $message }}</small> @enderror
            </label>

            <label class="field">
                <span>Due date</span>
                <input type="date" name="due_date" form="task-form" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
                @error('due_date') <small class="error">{{ $message }}</small> @enderror
            </label>

            <label class="field">
                <span>Label</span>
                <input type="text" name="label" form="task-form" value="{{ old('label', $task->label) }}" maxlength="30" list="label-options" placeholder="Design, Frontend, Backend">
                <datalist id="label-options">
                    @foreach ($labels as $option)
                        <option value="{{ $option }}"></option>
                    @endforeach
                </datalist>
                @error('label') <small class="error">{{ $message }}</small> @enderror
            </label>

            <button type="submit" form="task-form" class="btn btn-primary btn-block">Save changes</button>

            <p class="muted small meta-note">
                Created {{ $task->created_at->format('M j') }}@if ($task->creator) by {{ Str::before($task->creator->name, ' ') }}@endif<br>
                Last updated {{ $task->updated_at->diffForHumans() }}
            </p>
        </aside>
    </div>
@endsection
