@extends('layouts.app')

@section('title', 'Calendar')

@section('content')
    <header class="page-head">
        <div>
            <h1>{{ $month->format('F Y') }}</h1>
            <p class="muted">Due dates across your projects</p>
        </div>

        <div class="head-actions">
            <a class="chip {{ $mine ? '' : 'active' }}" href="{{ route('calendar.index', ['month' => $month->format('Y-m')]) }}">All tasks</a>
            <a class="chip {{ $mine ? 'active' : '' }}" href="{{ route('calendar.index', ['month' => $month->format('Y-m'), 'show' => 'mine']) }}">My tasks</a>
        </div>
    </header>

    <nav class="pager" aria-label="Months">
        <a class="btn btn-sm" href="{{ route('calendar.index', array_filter(['month' => $previous, 'show' => $mine ? 'mine' : null])) }}">← Previous</a>
        <a class="btn btn-ghost btn-sm" href="{{ route('calendar.index', array_filter(['show' => $mine ? 'mine' : null])) }}">Today</a>
        <a class="btn btn-sm" href="{{ route('calendar.index', array_filter(['month' => $next, 'show' => $mine ? 'mine' : null])) }}">Next →</a>
    </nav>

    {{-- A month grid on wide screens; on phones only the days that have tasks are listed. --}}
    <section class="calendar" aria-label="{{ $month->format('F Y') }}">
        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
            <div class="cal-weekday">{{ $weekday }}</div>
        @endforeach

        @foreach ($days as $day)
            <div @class([
                'cal-day',
                'cal-out' => ! $day['inMonth'],
                'cal-today' => $day['isToday'],
                'cal-empty' => $day['tasks']->isEmpty(),
            ])>
                <div class="cal-date">
                    <span class="cal-num">{{ $day['date']->day }}</span>
                    <span class="cal-full">{{ $day['date']->format('l, M j') }}</span>
                </div>

                @foreach ($day['tasks']->take(3) as $task)
                    <a href="{{ route('tasks.show', $task) }}"
                       @class(['cal-task', 'done' => $task->status === 'done', 'overdue' => $task->isOverdue()])
                       title="{{ $task->title }} · {{ $task->project->name }}">{{ $task->title }}</a>
                @endforeach

                @if ($day['tasks']->count() > 3)
                    <a class="cal-more" href="{{ route('tasks.index') }}">+{{ $day['tasks']->count() - 3 }} more</a>
                @endif
            </div>
        @endforeach
    </section>

    @unless ($hasTasks)
        <p class="empty">No tasks are due in this month.</p>
    @endunless
@endsection
