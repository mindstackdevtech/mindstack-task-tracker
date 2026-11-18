@extends('layouts.app')

@section('title', $project->name)

@php
    $chips = ['' => 'All', 'mine' => 'My tasks', 'high' => 'High priority', 'overdue' => 'Overdue'];
@endphp

@section('content')
    <header class="page-head">
        <div>
            <p class="muted small"><a href="{{ route('projects.index') }}">Projects</a> /</p>
            <h1>{{ $project->name }}</h1>
            @if ($project->description)
                <p class="muted">{{ $project->description }}</p>
            @endif
        </div>

        <div class="head-actions">
            <a class="avatars" href="#members" aria-label="{{ $members->count() }} {{ Str::plural('member', $members->count()) }}">
                @foreach ($members->take(5) as $member)
                    @include('partials.avatar', ['person' => $member, 'large' => true])
                @endforeach
                @if ($members->count() > 5)
                    <span class="avatar avatar-lg avatar-2">+{{ $members->count() - 5 }}</span>
                @endif
            </a>

            <details class="invite" @if ($errors->has('email')) open @endif>
                <summary class="btn">Invite</summary>
                <div class="invite-box">
                    <form method="POST" action="{{ route('projects.members.store', $project) }}" class="invite-form invite-form-first">
                        @csrf
                        <label for="invite-email" class="small"><strong>Invite by email</strong></label>
                        <input id="invite-email" type="email" name="email" value="{{ old('email') }}" placeholder="their@email.com" required>
                        @error('email') <small class="error">{{ $message }}</small> @enderror
                        <button type="submit" class="btn btn-primary btn-sm">Send invitation</button>
                        <p class="muted small">If they already have an account they are added right away and notified. Otherwise they get an email with a link to join.</p>
                    </form>

                    <label for="invite-link" class="small invite-divider"><strong>Or share the invite link</strong></label>
                    <input id="invite-link" type="text" readonly value="{{ route('projects.join', $project->invite_code) }}" onfocus="this.select()">
                    <button type="button" class="btn btn-sm" data-copy="#invite-link">Copy link</button>

                    @if ($isOwner)
                        <form method="POST" action="{{ route('projects.invite.reset', $project) }}" class="invite-form"
                              onsubmit="return confirm('Reset the invite link? The current link will stop working.')">
                            @csrf
                            <p class="muted small">Shared the link with the wrong person? Reset it and the old one stops working.</p>
                            <button type="submit" class="btn btn-danger btn-sm">Reset invite link</button>
                        </form>
                    @endif
                </div>
            </details>

            <a href="{{ route('projects.chat', $project) }}" class="btn">Chat</a>

            <a href="{{ route('tasks.create', ['project' => $project->id]) }}" class="btn btn-primary">New task</a>
        </div>
    </header>

    <div class="progress-line">
        <div class="bar"><span style="width: {{ $project->progress() }}%"></span></div>
        <span class="muted small">{{ $project->done_count }}/{{ $project->tasks_count }} done · {{ $project->progress() }}%</span>
    </div>

    <form method="GET" action="{{ route('projects.show', $project) }}" class="board-tools">
        <div class="search">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.5-4.5"/></svg>
            <input type="search" name="q" value="{{ $search }}" placeholder="Search tasks" aria-label="Search tasks">
        </div>
        @if ($filter)
            <input type="hidden" name="filter" value="{{ $filter }}">
        @endif

        <div class="chips">
            @foreach ($chips as $value => $text)
                <a href="{{ route('projects.show', array_filter(['project' => $project->id, 'filter' => $value, 'q' => $search])) }}"
                   @class(['chip', 'active' => (string) $filter === (string) $value])
                   @if ((string) $filter === (string) $value) aria-current="true" @endif>{{ $text }}</a>
            @endforeach
        </div>
    </form>

    <div class="board" data-board>
        @foreach ($statuses as $key => $label)
            @php $columnTasks = $tasks->get($key, collect()); @endphp
            <section class="column" data-status="{{ $key }}" aria-label="{{ $label }}">
                <div class="column-head">
                    <h2>{{ $label }}</h2>
                    <span class="count" data-count>{{ $columnTasks->count() }}</span>
                </div>

                <div class="column-body" data-dropzone>
                    @foreach ($columnTasks as $task)
                        <article @class(['task', 'is-done' => $task->status === 'done']) draggable="true" data-url="{{ route('tasks.status', $task) }}">
                            <div class="task-top">
                                @if ($task->label)
                                    <span class="tag">{{ $task->label }}</span>
                                @else
                                    <span></span>
                                @endif
                                <span class="pill pill-{{ $task->priority }}">{{ $task->priorityLabel() }}</span>
                            </div>

                            <a class="task-title" href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a>

                            @if ($task->checklist_items_count > 0)
                                <div class="mini-progress">
                                    <div class="bar"><span style="width: {{ round($task->checklist_done_count / $task->checklist_items_count * 100) }}%"></span></div>
                                    <span class="muted">{{ $task->checklist_done_count }}/{{ $task->checklist_items_count }}</span>
                                </div>
                            @endif

                            <div class="task-bottom">
                                @if ($task->assignee)
                                    @include('partials.avatar', ['person' => $task->assignee, 'large' => false])
                                @else
                                    <span class="muted small">Unassigned</span>
                                @endif

                                <div class="task-meta">
                                    @if ($task->comments_count > 0)
                                        <span title="Comments">
                                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M4 5h16v11H9l-5 4z"/></svg>
                                            {{ $task->comments_count }}
                                        </span>
                                    @endif
                                    @if ($task->due_date)
                                        <span @class(['due', 'overdue' => $task->isOverdue()])>{{ $task->due_date->format('M j') }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Shown on touch screens, where dragging is not available. --}}
                            <form method="POST" action="{{ route('tasks.status', $task) }}" class="task-move">
                                @csrf
                                @method('PATCH')
                                <label>
                                    <span class="muted small">Move to</span>
                                    <select name="status" onchange="this.form.submit()">
                                        @foreach ($statuses as $value => $text)
                                            <option value="{{ $value }}" @selected($task->status === $value)>{{ $text }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </form>
                        </article>
                    @endforeach
                </div>

                <a class="add-link" href="{{ route('tasks.create', ['project' => $project->id, 'status' => $key]) }}">+ Add task</a>
            </section>
        @endforeach
    </div>

    <div class="project-links">
        <a href="{{ route('projects.activity', $project) }}" class="btn btn-sm">Activity history</a>
        <a href="{{ route('projects.export', $project) }}" class="btn btn-sm">Export tasks (CSV)</a>
    </div>

    <section class="panel" id="members">
        <div class="panel-head">
            <h2>Members</h2>
            <span class="muted small">{{ $members->count() }}</span>
        </div>

        @foreach ($members as $member)
            <div class="member-row">
                @include('partials.avatar', ['person' => $member, 'large' => true])
                <div class="member-info">
                    <div class="row-title">{{ $member->name }}@if ($member->id === auth()->id()) (you)@endif</div>
                    <div class="muted small">{{ $member->email }}</div>
                </div>
                <span class="pill">{{ $member->pivot->role === 'owner' ? 'Owner' : 'Member' }}</span>

                @if ($member->id !== auth()->id())
                    <a href="{{ route('messages.show', $member) }}" class="btn btn-sm">Message</a>
                @endif

                @if ($member->pivot->role !== 'owner' && ($isOwner || $member->id === auth()->id()))
                    <form method="POST" action="{{ route('projects.members.destroy', [$project, $member]) }}"
                          onsubmit="return confirm('{{ $member->id === auth()->id() ? 'Leave this project?' : 'Remove this member from the project?' }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-sm">{{ $member->id === auth()->id() ? 'Leave' : 'Remove' }}</button>
                    </form>
                @endif
            </div>
        @endforeach
    </section>

    <details class="panel disclosure" @if ($errors->hasAny(['name', 'description'])) open @endif>
        <summary>Project settings</summary>

        <form method="POST" action="{{ route('projects.update', $project) }}" class="form">
            @csrf
            @method('PUT')
            <label class="field">
                <span>Name</span>
                <input type="text" name="name" value="{{ old('name', $project->name) }}" required maxlength="255">
                @error('name') <small class="error">{{ $message }}</small> @enderror
            </label>
            <label class="field">
                <span>Description</span>
                <textarea name="description" rows="2">{{ old('description', $project->description) }}</textarea>
                @error('description') <small class="error">{{ $message }}</small> @enderror
            </label>
            <div><button type="submit" class="btn">Save changes</button></div>
        </form>

        @if ($isOwner)
            <form method="POST" action="{{ route('projects.destroy', $project) }}" class="danger-zone"
                  onsubmit="return confirm('Delete this project and all of its tasks? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <span class="muted small">Deleting a project also deletes its tasks.</span>
                <button type="submit" class="btn btn-danger btn-sm">Delete project</button>
            </form>
        @endif
    </details>
@endsection

@push('scripts')
    <script src="{{ asset('js/board.js') }}" defer></script>
@endpush
