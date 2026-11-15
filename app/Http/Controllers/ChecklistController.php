<?php

namespace App\Http\Controllers;

use App\Models\ChecklistItem;
use App\Models\Task;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $task->project->ensureMember($request->user());

        $data = $request->validate([
            'item_title' => ['required', 'string', 'max:255'],
        ]);

        $task->checklistItems()->create(['title' => $data['item_title']]);

        return redirect()->to(route('tasks.show', $task).'#checklist');
    }

    public function toggle(Request $request, ChecklistItem $item)
    {
        $item->task->project->ensureMember($request->user());

        $item->update(['is_done' => ! $item->is_done]);

        return redirect()->to(route('tasks.show', $item->task_id).'#checklist');
    }

    public function destroy(Request $request, ChecklistItem $item)
    {
        $item->task->project->ensureMember($request->user());

        $taskId = $item->task_id;
        $item->delete();

        return redirect()->to(route('tasks.show', $taskId).'#checklist');
    }
}
