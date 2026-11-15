<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $fillable = ['name', 'description', 'created_by'];

    protected static function booted(): void
    {
        // Every project gets a private code used in its invite link.
        static::creating(function (Project $project) {
            $project->invite_code ??= Str::random(24);
        });
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    /** Only the projects this user belongs to. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->whereHas('members', fn (Builder $members) => $members->whereKey($user->id));
    }

    /** Ids of the projects this user belongs to. */
    public static function idsFor(User $user): Collection
    {
        return static::visibleTo($user)->pluck('projects.id');
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->whereKey($user->id)->exists();
    }

    public function isOwner(User $user): bool
    {
        return $this->members()->whereKey($user->id)->wherePivot('role', 'owner')->exists();
    }

    /** Stop with "403 Forbidden" unless this user belongs to the project. */
    public function ensureMember(?User $user): void
    {
        abort_unless($user !== null && $this->hasMember($user), 403, 'You are not a member of this project.');
    }

    /**
     * Percentage of tasks done. Expects tasks_count and done_count
     * to be loaded with withCount() (see the controllers).
     */
    public function progress(): int
    {
        $total = (int) ($this->tasks_count ?? 0);

        return $total === 0 ? 0 : (int) round(($this->done_count ?? 0) / $total * 100);
    }
}
