<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Everything on the dashboard is limited to the projects you belong to.
        $projectIds = Project::idsFor($user);

        $counts = Task::whereIn('project_id', $projectIds)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $overdue = Task::whereIn('project_id', $projectIds)
            ->where('status', '!=', 'done')
            ->whereDate('due_date', '<', today())
            ->count();

        // Open tasks other people (or you) have given to you.
        $myTasks = Task::with(['project', 'creator'])
            ->whereIn('project_id', $projectIds)
            ->where('assigned_to', $user->id)
            ->where('status', '!=', 'done')
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        // Open tasks that are overdue or due within the next 7 days.
        $dueSoon = Task::with(['project', 'assignee'])
            ->whereIn('project_id', $projectIds)
            ->where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', today()->addDays(7))
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        $activities = Activity::with(['user', 'project'])
            ->whereIn('project_id', $projectIds)
            ->latest()
            ->latest('id')
            ->limit(6)
            ->get();

        $openByUser = Task::whereIn('project_id', $projectIds)
            ->selectRaw('assigned_to, count(*) as total')
            ->where('status', '!=', 'done')
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->pluck('total', 'assigned_to');

        $workload = User::whereIn('id', $openByUser->keys())
            ->orderBy('name')
            ->get()
            ->map(fn (User $member) => ['user' => $member, 'total' => (int) $openByUser[$member->id]]);

        return view('dashboard', [
            'counts' => $counts,
            'total' => (int) $counts->sum(),
            'overdue' => $overdue,
            'myTasks' => $myTasks,
            'dueSoon' => $dueSoon,
            'activities' => $activities,
            'workload' => $workload,
            'maxLoad' => max(1, (int) $openByUser->max()),
            'statuses' => Task::STATUSES,
        ]);
    }
}
