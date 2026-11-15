<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $task->project->ensureMember($request->user());

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $task->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        Activity::record($task->project_id, 'commented on "'.$task->title.'"', $task);

        return redirect()->to(route('tasks.show', $task).'#comments');
    }

    public function destroy(Request $request, Comment $comment)
    {
        // Only the person who wrote a comment can remove it.
        abort_unless($comment->user_id === $request->user()->id, 403);

        $taskId = $comment->task_id;
        $comment->delete();

        return redirect()->to(route('tasks.show', $taskId).'#comments');
    }
}
