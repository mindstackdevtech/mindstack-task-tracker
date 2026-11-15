@extends('layouts.app')

@section('title', 'New task')

@section('content')
    <header class="page-head">
        <div>
            <p class="muted small"><a href="{{ route('tasks.index') }}">Tasks</a> /</p>
            <h1>New task</h1>
        </div>
    </header>

    <div class="panel narrow">
        <form method="POST" action="{{ route('tasks.store') }}" class="form">
            @csrf

            {{-- Changing the project reloads the page so Assignee lists that project's members. --}}
            <label class="field">
                <span>Project</span>
                <select name="project_id" required
                        onchange="window.location = '{{ route('tasks.create') }}?status={{ $task->status }}&project=' + this.value">
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" @selected((string) old('project_id', $task->project_id) === (string) $project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
                @error('project_id') <small class="error">{{ $message }}</small> @enderror
            </label>

            <label class="field">
                <span>Title</span>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="255" autofocus>
                @error('title') <small class="error">{{ $message }}</small> @enderror
            </label>

            <label class="field">
                <span>Description <em class="muted">(optional)</em></span>
                <textarea name="description" rows="4">{{ old('description') }}</textarea>
                @error('description') <small class="error">{{ $message }}</small> @enderror
            </label>

            <div class="field-row">
                <label class="field">
                    <span>Assign to <em class="muted">(project members)</em></span>
                    <select name="assigned_to">
                        <option value="">Unassigned</option>
                        @foreach ($users as $member)
                            <option value="{{ $member->id }}" @selected((string) old('assigned_to') === (string) $member->id)>{{ $member->name }}</option>
                        @endforeach
                    </select>
                    @error('assigned_to') <small class="error">{{ $message }}</small> @enderror
                </label>
            </div>

            <div class="field-row">
                <label class="field">
                    <span>Status</span>
                    <select name="status" required>
                        @foreach ($statuses as $value => $text)
                            <option value="{{ $value }}" @selected(old('status', $task->status) === $value)>{{ $text }}</option>
                        @endforeach
                    </select>
                    @error('status') <small class="error">{{ $message }}</small> @enderror
                </label>

                <label class="field">
                    <span>Priority</span>
                    <select name="priority" required>
                        @foreach ($priorities as $value => $text)
                            <option value="{{ $value }}" @selected(old('priority', $task->priority) === $value)>{{ $text }}</option>
                        @endforeach
                    </select>
                    @error('priority') <small class="error">{{ $message }}</small> @enderror
                </label>
            </div>

            <div class="field-row">
                <label class="field">
                    <span>Due date <em class="muted">(optional)</em></span>
                    <input type="date" name="due_date" value="{{ old('due_date') }}">
                    @error('due_date') <small class="error">{{ $message }}</small> @enderror
                </label>

                <label class="field">
                    <span>Label <em class="muted">(optional)</em></span>
                    <input type="text" name="label" value="{{ old('label') }}" maxlength="30" list="label-options" placeholder="Design, Frontend, Backend">
                    <datalist id="label-options">
                        @foreach ($labels as $option)
                            <option value="{{ $option }}"></option>
                        @endforeach
                    </datalist>
                    @error('label') <small class="error">{{ $message }}</small> @enderror
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add task</button>
                <a href="{{ $task->project_id ? route('projects.show', $task->project_id) : route('tasks.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
@endsection
