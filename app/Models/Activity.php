<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = ['project_id', 'user_id', 'task_id', 'description'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Record something the logged-in user just did in a project, written as a
     * phrase that reads after their name: 'moved "Fix login" to In Review'.
     */
    public static function record(int $projectId, string $description, ?Task $task = null): void
    {
        static::create([
            'project_id' => $projectId,
            'user_id' => auth()->id(),
            'task_id' => $task?->id,
            'description' => mb_substr($description, 0, 255),
        ]);
    }
}
