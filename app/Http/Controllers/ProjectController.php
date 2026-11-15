<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::visibleTo($request->user())
            ->withCount([
                'members',
                'tasks',
                'tasks as done_count' => fn ($query) => $query->where('status', 'done'),
            ])
            ->latest()
            ->get();

        return view('projects.index', compact('projects'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        $project = Project::create($data);

        // Whoever creates a project owns it.
        $project->members()->attach($request->user()->id, ['role' => 'owner']);

        return redirect()->route('projects.show', $project)->with('status', 'Project created. Use Invite to add your team.');
    }

    public function show(Request $request, Project $project)
    {
        $project->ensureMember($request->user());

        $project->loadCount([
            'tasks',
            'tasks as done_count' => fn ($query) => $query->where('status', 'done'),
        ]);

        $filter = $request->query('filter');
        $search = trim((string) $request->query('q'));

        $tasks = $project->tasks()
            ->with('assignee')
            ->withCount([
                'comments',
                'checklistItems',
                'checklistItems as checklist_done_count' => fn ($query) => $query->where('is_done', true),
            ])
            ->when($search !== '', fn ($q) => $q->where('title', 'like', '%'.$search.'%'))
            ->when($filter === 'mine', fn ($q) => $q->where('assigned_to', $request->user()->id))
            ->when($filter === 'high', fn ($q) => $q->where('priority', 'high'))
            ->when($filter === 'overdue', fn ($q) => $q->where('status', '!=', 'done')->whereDate('due_date', '<', today()))
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get()
            ->groupBy('status');

        return view('projects.show', [
            'project' => $project,
            'tasks' => $tasks,
            'statuses' => Task::STATUSES,
            'filter' => $filter,
            'search' => $search,
            'members' => $project->members()->orderBy('project_user.id')->get(),
            'isOwner' => $project->isOwner($request->user()),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $project->ensureMember($request->user());
        $project->update($this->validated($request));

        return redirect()->route('projects.show', $project)->with('status', 'Project updated.');
    }

    public function destroy(Request $request, Project $project)
    {
        abort_unless($project->isOwner($request->user()), 403, 'Only the project owner can delete it.');

        $project->delete();

        return redirect()->route('projects.index')->with('status', 'Project deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
