<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Project;
use App\Models\User;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        // Tell the owner someone new is in.
        foreach ($project->members()->wherePivot('role', 'owner')->get() as $owner) {
            Notifier::send(
                $owner,
                $user->name.' joined '.$project->name,
                $user->name.' joined your project using the invite link.',
                route('projects.show', $project).'#members',
            );
        }

        return redirect()->route('projects.show', $project)->with('status', 'You joined '.$project->name.'.');
    }

    /**
     * Invite by email. Someone who already has an account is added straight
     * away and notified. Anyone else gets an invitation email with the
     * project's join link.
     */
    public function store(Request $request, Project $project)
    {
        $me = $request->user();
        $project->ensureMember($me);

        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if ($user === null) {
            $sent = Notifier::mailTo(
                $data['email'],
                $me->name.' invited you to '.$project->name,
                $me->name.' invited you to join the project "'.$project->name.'" on MindStack Task Tracker. Create an account or log in and you will be added to the project.',
                route('projects.join', $project->invite_code),
                'Join the project',
            );

            return $sent
                ? back()->with('status', 'An invitation email was sent to '.$data['email'].'.')
                : back()->withErrors(['email' => 'The invitation email could not be sent. Copy the invite link and send it yourself.'])->onlyInput('email');
        }

        if ($project->hasMember($user)) {
            return back()->with('status', $user->name.' is already in this project.');
        }

        $project->members()->attach($user->id, ['role' => 'member']);
        Activity::record($project->id, 'added '.$user->name.' to the project');

        Notifier::send(
            $user,
            'You were added to '.$project->name,
            $me->name.' added you to the project "'.$project->name.'".',
            route('projects.show', $project),
        );

        return back()->with('status', $user->name.' was added to the project and notified.');
    }

    /**
     * The owner can replace the invite link. The old link stops working,
     * which is what you want if it was shared with the wrong people.
     */
    public function resetInvite(Request $request, Project $project)
    {
        abort_unless($project->isOwner($request->user()), 403, 'Only the project owner can reset the invite link.');

        $project->invite_code = Str::random(24);
        $project->save();

        Activity::record($project->id, 'reset the invite link');

        return back()->with('status', 'The invite link was reset. The old link no longer works.');
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
