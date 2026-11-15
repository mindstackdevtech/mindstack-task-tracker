@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <header class="page-head">
        <div>
            <h1>Projects</h1>
            <p class="muted">{{ $projects->count() }} {{ Str::plural('project', $projects->count()) }} you belong to. To join someone else's, open the invite link they send you.</p>
        </div>
    </header>

    <details class="panel disclosure" @if ($errors->any() || $projects->isEmpty()) open @endif>
        <summary>New project</summary>
        <form method="POST" action="{{ route('projects.store') }}" class="form">
            @csrf
            <label class="field">
                <span>Name</span>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="255">
                @error('name') <small class="error">{{ $message }}</small> @enderror
            </label>
            <label class="field">
                <span>Description <em class="muted">(optional)</em></span>
                <textarea name="description" rows="2">{{ old('description') }}</textarea>
                @error('description') <small class="error">{{ $message }}</small> @enderror
            </label>
            <div><button type="submit" class="btn btn-primary">Create project</button></div>
        </form>
    </details>

    <div class="grid">
        @foreach ($projects as $project)
            <a class="card" href="{{ route('projects.show', $project) }}">
                <h3>{{ $project->name }}</h3>
                <p class="muted small clamp">{{ $project->description ?: 'No description' }}</p>
                <div class="bar"><span style="width: {{ $project->progress() }}%"></span></div>
                <div class="row-line muted small">
                    <span>{{ $project->done_count }}/{{ $project->tasks_count }} tasks done</span>
                    <span>{{ $project->members_count }} {{ Str::plural('member', $project->members_count) }}</span>
                </div>
            </a>
        @endforeach
    </div>
@endsection
