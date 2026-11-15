<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tasks = Task::with(['project', 'assignee'])
            ->whereIn('project_id', Project::idsFor($user))
            ->when($request->filled('project'), fn ($q) => $q->where('project_id', $request->query('project')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->query('assignee') === 'me', fn ($q) => $q->where('assigned_to', $user->id))
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->query('search').'%'))
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'projects' => Project::visibleTo($user)->orderBy('name')->get(),
            'statuses' => Task::STATUSES,
        ]);
    }

    public function create(Request $request)
    {
        $projects = Project::visibleTo($request->user())->orderBy('name')->get();

        if ($projects->isEmpty()) {
            return redirect()->route('projects.index')->with('status', 'Create a project first, then add tasks to it.');
        }

        // Use the project from the link if you belong to it, otherwise the first one.
        $project = $projects->firstWhere('id', (int) $request->query('project')) ?? $projects->first();
        $status = (string) $request->query('status');

        $task = new Task([
            'project_id' => $project->id,
            'status' => array_key_exists($status, Task::STATUSES) ? $status : 'todo',
            'priority' => 'medium',
        ]);

        return view('tasks.form', [
            'task' => $task,
            'projects' => $projects,
            'users' => $project->members()->orderBy('name')->get(),
            'statuses' => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
            'labels' => $this->labels($project->id),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'project_id' => ['required', Rule::in(Project::idsFor($user)->all())],
        ]);

        $data = $this->validated($request, (int) $request->input('project_id'));
        $data['project_id'] = (int) $request->input('project_id');
        $data['created_by'] = $user->id;

        $task = Task::create($data);

        Activity::record($task->project_id, 'created "'.$task->title.'"', $task);
        $this->recordAssignment($task);

        return redirect()->route('projects.show', $task->project_id)->with('status', 'Task added.');
    }

    public function show(Request $request, Task $task)
    {
        $task->load(['project', 'assignee', 'creator', 'checklistItems', 'comments.user']);
        $task->project->ensureMember($request->user());

        return view('tasks.show', [
            'task' => $task,
            'users' => $task->project->members()->orderBy('name')->get(),
            'statuses' => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
            'labels' => $this->labels($task->project_id),
        ]);
    }

    public function update(Request $request, Task $task)
    {
        $task->project->ensureMember($request->user());

        $oldStatus = $task->status;
        $oldAssignee = (int) $task->assigned_to;

        $task->update($this->validated($request, $task->project_id));

        $statusChanged = $task->status !== $oldStatus;
        $assigneeChanged = (int) $task->assigned_to !== $oldAssignee;

        if ($statusChanged) {
            $this->recordStatus($task);
        }
        if ($assigneeChanged) {
            $this->recordAssignment($task);
        }
        if (! $statusChanged && ! $assigneeChanged) {
            Activity::record($task->project_id, 'updated "'.$task->title.'"', $task);
        }

        return redirect()->route('tasks.show', $task)->with('status', 'Task updated.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $task->project->ensureMember($request->user());

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Task::STATUSES))],
        ]);

        if ($task->status !== $data['status']) {
            $task->update($data);
            $this->recordStatus($task);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'status' => $task->status]);
        }

        return back()->with('status', 'Task moved to '.$task->statusLabel().'.');
    }

    public function destroy(Request $request, Task $task)
    {
        $task->project->ensureMember($request->user());

        $projectId = $task->project_id;
        $title = $task->title;
        $task->delete();

        Activity::record($projectId, 'deleted "'.$title.'"');

        return redirect()->route('projects.show', $projectId)->with('status', 'Task deleted.');
    }

    private function recordStatus(Task $task): void
    {
        $description = $task->status === 'done'
            ? 'completed "'.$task->title.'"'
            : 'moved "'.$task->title.'" to '.$task->statusLabel();

        Activity::record($task->project_id, $description, $task);
    }

    /** Notes in the activity feed when a task is given to someone else. */
    private function recordAssignment(Task $task): void
    {
        if ($task->assigned_to === null || (int) $task->assigned_to === (int) auth()->id()) {
            return;
        }

        $name = Str::before((string) User::find($task->assigned_to)?->name, ' ');
        Activity::record($task->project_id, 'assigned "'.$task->title.'" to '.$name, $task);
    }

    private function labels(int $projectId)
    {
        return Task::where('project_id', $projectId)
            ->whereNotNull('label')
            ->distinct()
            ->orderBy('label')
            ->pluck('label');
    }

    private function validated(Request $request, int $projectId): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(array_keys(Task::STATUSES))],
            'priority' => ['required', Rule::in(array_keys(Task::PRIORITIES))],
            'label' => ['nullable', 'string', 'max:30'],
            'due_date' => ['nullable', 'date'],
            // You can only give a task to someone who is in the project.
            'assigned_to' => [
                'nullable',
                Rule::exists('project_user', 'user_id')->where('project_id', $projectId),
            ],
        ], [
            'assigned_to.exists' => 'That person is not a member of this project.',
        ]);
    }
}
