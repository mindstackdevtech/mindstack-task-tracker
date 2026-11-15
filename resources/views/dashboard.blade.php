@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $firstName = Str::before(auth()->user()->name, ' ');
@endphp

@section('content')
    <header class="page-head">
        <div>
            <h1>{{ $greeting }}, {{ $firstName }}</h1>
            <p class="muted">
                {{ now()->format('l, F j') }}
                @if ($overdue > 0)
                    · {{ $overdue }} {{ Str::plural('task', $overdue) }} overdue
                @endif
            </p>
        </div>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">New task</a>
    </header>

    <section class="stats" aria-label="Task counts">
        @foreach ($statuses as $key => $label)
            <a class="stat" href="{{ route('tasks.index', ['status' => $key]) }}">
                <span class="stat-num">{{ $counts[$key] ?? 0 }}</span>
                <span class="stat-label">{{ $label }}</span>
            </a>
        @endforeach
        <div @class(['stat', 'stat-alert' => $overdue > 0])>
            <span class="stat-num">{{ $overdue }}</span>
            <span class="stat-label">Overdue</span>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2>Status breakdown</h2>
            <span class="muted small">{{ $total }} {{ Str::plural('task', $total) }}</span>
        </div>

        @if ($total > 0)
            <div class="breakdown" role="img" aria-label="Tasks by status">
                @foreach ($statuses as $key => $label)
                    @if (($counts[$key] ?? 0) > 0)
                        <span class="seg seg-{{ $key }}" style="flex: {{ $counts[$key] }}" title="{{ $label }}: {{ $counts[$key] }}"></span>
                    @endif
                @endforeach
            </div>
            <div class="legend">
                @foreach ($statuses as $key => $label)
                    <span><i class="seg-{{ $key }}"></i>{{ $label }} ({{ $counts[$key] ?? 0 }})</span>
                @endforeach
            </div>
        @else
            <p class="empty">No tasks yet. <a href="{{ route('projects.index') }}">Create a project</a> to get started.</p>
        @endif
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2>Assigned to you</h2>
            <a href="{{ route('tasks.index', ['assignee' => 'me']) }}" class="small">View all</a>
        </div>

        @forelse ($myTasks as $task)
            <a class="row" href="{{ route('tasks.show', $task) }}">
                <div>
                    <div class="row-title">{{ $task->title }}</div>
                    <div class="muted small">
                        {{ $task->project->name }} · {{ $task->statusLabel() }}
                        @if ($task->creator && $task->created_by !== auth()->id())
                            · from {{ Str::before($task->creator->name, ' ') }}
                        @endif
                    </div>
                </div>
                @if ($task->due_date)
                    <span @class(['due', 'overdue' => $task->isOverdue()])>{{ $task->due_date->format('M j') }}</span>
                @endif
            </a>
        @empty
            <p class="empty">Nothing open is assigned to you.</p>
        @endforelse
    </section>

    <div class="two-col">
        <section class="panel">
            <div class="panel-head">
                <h2>Due this week</h2>
                <a href="{{ route('tasks.index') }}" class="small">View all</a>
            </div>

            @forelse ($dueSoon as $task)
                <a class="row" href="{{ route('tasks.show', $task) }}">
                    <div>
                        <div class="row-title">{{ $task->title }}</div>
                        <div class="muted small">
                            {{ $task->project->name }} · {{ $task->statusLabel() }}
                            · {{ $task->assigned_to === auth()->id() ? 'You' : ($task->assignee?->name ?? 'Unassigned') }}
                        </div>
                    </div>
                    <span @class(['due', 'overdue' => $task->isOverdue()])>
                        @if ($task->isOverdue()) Overdue · @endif{{ $task->due_date->format('M j') }}
                    </span>
                </a>
            @empty
                <p class="empty">Nothing is due in the next 7 days.</p>
            @endforelse
        </section>

        <div class="stack">
            <section class="panel">
                <div class="panel-head">
                    <h2>Recent activity</h2>
                </div>

                @forelse ($activities as $activity)
                    <div class="activity">
                        @include('partials.avatar', ['person' => $activity->user])
                        <div>
                            <strong>{{ $activity->user_id === auth()->id() ? 'You' : Str::before($activity->user?->name ?? 'Someone', ' ') }}</strong>
                            @if ($activity->task_id)
                                <a href="{{ route('tasks.show', $activity->task_id) }}">{{ $activity->description }}</a>
                            @else
                                {{ $activity->description }}
                            @endif
                            <div class="muted small">{{ $activity->project?->name }} · {{ $activity->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <p class="empty">Activity shows up here as your team works.</p>
                @endforelse
            </section>

            <section class="panel">
                <div class="panel-head">
                    <h2>Team workload</h2>
                </div>

                @forelse ($workload as $row)
                    <div class="workload">
                        <span class="workload-name">{{ Str::before($row['user']->name, ' ') }}</span>
                        <div class="bar"><span style="width: {{ round($row['total'] / $maxLoad * 100) }}%"></span></div>
                        <span class="muted small">{{ $row['total'] }} open</span>
                    </div>
                @empty
                    <p class="empty">No open tasks are assigned yet.</p>
                @endforelse
            </section>
        </div>
    </div>
@endsection
