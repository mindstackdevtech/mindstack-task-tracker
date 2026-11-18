<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use App\Support\Security;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectToolsController extends Controller
{
    /** Everything that has happened in a project, newest first. */
    public function activity(Request $request, Project $project)
    {
        $project->ensureMember($request->user());

        $activities = Activity::with('user')
            ->where('project_id', $project->id)
            ->latest()
            ->latest('id')
            ->paginate(30);

        return view('projects.activity', compact('project', 'activities'));
    }

    /** Downloads the project's tasks as a spreadsheet file (CSV). */
    public function export(Request $request, Project $project)
    {
        $project->ensureMember($request->user());

        $filename = (Str::slug($project->name) ?: 'project').'-tasks-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($project) {
            $out = fopen('php://output', 'w');

            // Lets Excel read accented letters correctly.
            fwrite($out, "\xEF\xBB\xBF");

            $row = fn (array $cells) => fputcsv($out, array_map([Security::class, 'csvCell'], $cells), ',', '"', '\\');

            $row(['Title', 'Status', 'Priority', 'Label', 'Assignee', 'Due date', 'Created by', 'Created on', 'Description']);

            $tasks = Task::with(['assignee', 'creator'])
                ->where('project_id', $project->id)
                ->orderBy('id')
                ->get();

            foreach ($tasks as $task) {
                $row([
                    $task->title,
                    $task->statusLabel(),
                    $task->priorityLabel(),
                    $task->label,
                    $task->assignee?->name,
                    $task->due_date?->format('Y-m-d'),
                    $task->creator?->name,
                    $task->created_at->format('Y-m-d'),
                    $task->description,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
