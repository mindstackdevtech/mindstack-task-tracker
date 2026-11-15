@extends('layouts.app')

@section('title', 'All tasks')

@section('content')
    <header class="page-head">
        <div>
            <h1>All tasks</h1>
            <p class="muted">{{ $tasks->total() }} {{ Str::plural('task', $tasks->total()) }}</p>
        </div>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">New task</a>
    </header>

    <form method="GET" action="{{ route('tasks.index') }}" class="filters">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by title" aria-label="Search by title">

        <select name="project" aria-label="Project">
            <option value="">All projects</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" @selected((string) request('project') === (string) $project->id)>{{ $project->name }}</option>
            @endforeach
        </select>

        <select name="status" aria-label="Status">
            <option value="">Any status</option>
            @foreach ($statuses as $value => $text)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $text }}</option>
            @endforeach
        </select>

        <select name="assignee" aria-label="Assignee">
            <option value="">Everyone</option>
            <option value="me" @selected(request('assignee') === 'me')>Assigned to me</option>
        </select>

        <button type="submit" class="btn">Filter</button>
        @if (request()->hasAny(['search', 'project', 'status', 'assignee']))
            <a href="{{ route('tasks.index') }}" class="small">Clear</a>
        @endif
    </form>

    {{-- A table on wide screens; each row turns into a card on phones (see app.css). --}}
    <div class="panel table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Assignee</th>
                    <th>Due</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tasks as $task)
                    <tr>
                        <td data-label="Task">
                            <a class="row-title" href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a>
                            @if ($task->label)
                                <span class="tag">{{ $task->label }}</span>
                            @endif
                        </td>
                        <td data-label="Project"><a href="{{ route('projects.show', $task->project) }}">{{ $task->project->name }}</a></td>
                        <td data-label="Status"><span class="pill">{{ $task->statusLabel() }}</span></td>
                        <td data-label="Priority"><span class="pill pill-{{ $task->priority }}">{{ $task->priorityLabel() }}</span></td>
                        <td data-label="Assignee" class="muted">{{ $task->assignee?->name ?? 'Unassigned' }}</td>
                        <td data-label="Due">
                            @if ($task->due_date)
                                <span @class(['due', 'overdue' => $task->isOverdue()])>{{ $task->due_date->format('M j, Y') }}</span>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No tasks match.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($tasks->hasPages())
        <nav class="pager" aria-label="Pages">
            @if ($tasks->onFirstPage())
                <span class="muted">← Previous</span>
            @else
                <a href="{{ $tasks->previousPageUrl() }}">← Previous</a>
            @endif

            <span class="muted small">Page {{ $tasks->currentPage() }} of {{ $tasks->lastPage() }}</span>

            @if ($tasks->hasMorePages())
                <a href="{{ $tasks->nextPageUrl() }}">Next →</a>
            @else
                <span class="muted">Next →</span>
            @endif
        </nav>
    @endif
@endsection
