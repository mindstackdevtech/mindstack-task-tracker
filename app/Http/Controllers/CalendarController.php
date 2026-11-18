<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CalendarController extends Controller
{
    /** A month of due dates across the projects you belong to. */
    public function index(Request $request)
    {
        $requested = (string) $request->query('month');

        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $requested)
            ? Carbon::createFromFormat('Y-m-d', $requested.'-01')->startOfDay()
            : today()->startOfMonth();

        // The grid always shows whole weeks, Monday to Sunday.
        $gridStart = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $tasks = Task::with('project')
            ->whereIn('project_id', Project::idsFor($request->user()))
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', $gridStart->toDateString())
            ->whereDate('due_date', '<=', $gridEnd->toDateString())
            ->when($request->query('show') === 'mine', fn ($q) => $q->where('assigned_to', $request->user()->id))
            ->orderBy('due_date')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Task $task) => $task->due_date->toDateString());

        $days = [];
        for ($day = $gridStart->copy(); $day->lte($gridEnd); $day->addDay()) {
            $days[] = [
                'date' => $day->copy(),
                'inMonth' => $day->month === $month->month,
                'isToday' => $day->isToday(),
                'tasks' => $tasks->get($day->toDateString(), collect()),
            ];
        }

        return view('calendar.index', [
            'month' => $month,
            'days' => $days,
            'previous' => $month->copy()->subMonth()->format('Y-m'),
            'next' => $month->copy()->addMonth()->format('Y-m'),
            'mine' => $request->query('show') === 'mine',
            'hasTasks' => $tasks->isNotEmpty(),
        ]);
    }
}
