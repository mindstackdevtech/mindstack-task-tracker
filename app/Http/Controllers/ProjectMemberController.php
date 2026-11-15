<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    /** Opening a project's invite link adds you to that project. */
    public function join(Request $request, string $code)
    {
        $project = Project::where('invite_code', $code)->firstOrFail();
        $user = $request->user();

        if ($project->hasMember($user)) {
            return redirect()->route('projects.show', $project)->with('status', 'You are already in this project.');
        }

        $project->members()->attach($user->id, ['role' => 'member']);
        Activity::record($project->id, 'joined the project');

        return redirect()->route('projects.show', $project)->with('status', 'You joined '.$project->name.'.');
    }

    /** Add someone who already has an account, by their email. */
    public function store(Request $request, Project $project)
    {
        $project->ensureMember($request->user());

        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if ($user === null) {
            return back()->withErrors([
                'email' => 'No account uses that email yet. Send them the invite link instead.',
            ])->onlyInput('email');
        }

        if ($project->hasMember($user)) {
            return back()->with('status', $user->name.' is already in this project.');
        }

        $project->members()->attach($user->id, ['role' => 'member']);
        Activity::record($project->id, 'added '.$user->name.' to the project');

        return back()->with('status', $user->name.' was added to the project.');
    }

    /** The owner can remove a member; a member can leave. */
    public function destroy(Request $request, Project $project, User $user)
    {
        $me = $request->user();
        $project->ensureMember($me);

        $leaving = $me->id === $user->id;
        abort_unless($leaving || $project->isOwner($me), 403, 'Only the project owner can remove members.');

        if ($project->isOwner($user)) {
            return back()->with('status', 'The owner cannot leave. Delete the project instead.');
        }

        $project->members()->detach($user->id);

        // Their tasks stay in the project but become unassigned.
        $project->tasks()->where('assigned_to', $user->id)->update(['assigned_to' => null]);

        Activity::record($project->id, $leaving ? 'left the project' : 'removed '.$user->name.' from the project');

        return $leaving
            ? redirect()->route('projects.index')->with('status', 'You left '.$project->name.'.')
            : back()->with('status', $user->name.' was removed.');
    }
}
