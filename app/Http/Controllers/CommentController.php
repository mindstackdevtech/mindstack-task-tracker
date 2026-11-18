<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $me = $request->user();
        $task->project->ensureMember($me);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $task->comments()->create([
            'user_id' => $me->id,
            'body' => $data['body'],
        ]);

        Activity::record($task->project_id, 'commented on "'.$task->title.'"', $task);

        // Tell the person doing the task and the person who created it.
        $recipientIds = collect([$task->assigned_to, $task->created_by])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->reject(fn ($id) => $id === $me->id);

        foreach (User::whereIn('id', $recipientIds)->get() as $recipient) {
            Notifier::send(
                $recipient,
                $me->name.' commented on "'.$task->title.'"',
                Str::limit($data['body'], 140),
                route('tasks.show', $task).'#comments',
            );
        }

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
