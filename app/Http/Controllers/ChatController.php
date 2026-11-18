<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Project;
use App\Notifications\AppNotification;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /** The project's team chat page. */
    public function show(Request $request, Project $project)
    {
        $me = $request->user();
        $project->ensureMember($me);

        $members = $project->members()->orderBy('name')->get();

        $messages = Message::with('user')
            ->where('project_id', $project->id)
            ->latest('id')
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        return view('projects.chat', [
            'project' => $project,
            'messages' => $messages->map(fn (Message $m) => $this->present($m, $me, $members)),
            'lastId' => (int) ($messages->last()->id ?? 0),
            // Names offered when someone types "@" (everyone except yourself).
            'mentionNames' => $members->where('id', '!=', $me->id)->pluck('name')->values(),
        ]);
    }

    /** New messages since the last one the page has (the page asks every few seconds). */
    public function feed(Request $request, Project $project)
    {
        $me = $request->user();
        $project->ensureMember($me);

        $members = $project->members()->get();

        $messages = Message::with('user')
            ->where('project_id', $project->id)
            ->where('id', '>', (int) $request->query('after', 0))
            ->orderBy('id')
            ->limit(100)
            ->get();

        return response()->json([
            'messages' => $messages->map(fn (Message $m) => $this->present($m, $me, $members))->values(),
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $me = $request->user();
        $project->ensureMember($me);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = Message::create([
            'project_id' => $project->id,
            'user_id' => $me->id,
            'body' => $data['body'],
        ]);

        $members = $project->members()->get();
        $this->notify($project, $members, $me, $data['body']);

        if ($request->expectsJson()) {
            return response()->json(['message' => $this->present($message->load('user'), $me, $members)]);
        }

        return redirect()->route('projects.chat', $project);
    }

    /**
     * People who were @mentioned get their own notification and an email.
     * Everyone else gets one bell notification for the chat, not one per
     * message: nothing new is added while they still have an unread one.
     */
    private function notify(Project $project, Collection $members, $sender, string $body): void
    {
        $url = route('projects.chat', $project);
        $everyone = (bool) preg_match('/(^|\s)@(everyone|all)\b/i', $body);

        foreach ($members as $member) {
            if ($member->id === $sender->id) {
                continue;
            }

            if ($everyone || $this->mentions($body, $member->name)) {
                Notifier::send(
                    $member,
                    $sender->name.' mentioned you in '.$project->name,
                    Str::limit($body, 160),
                    $url,
                );

                continue;
            }

            $alreadyTold = $member->unreadNotifications
                ->contains(fn ($notification) => ($notification->data['url'] ?? '') === $url);

            if (! $alreadyTold) {
                $member->notify(new AppNotification(
                    'New messages in '.$project->name,
                    $sender->name.' wrote in the team chat.',
                    $url,
                    ['database'],
                ));
            }
        }
    }

    /** True when the message contains "@Full Name" for this name. */
    private function mentions(string $body, string $name): bool
    {
        return $name !== '' && mb_stripos($body, '@'.$name) !== false;
    }

    /** The shape both the page and the live feed use for a message. */
    private function present(Message $message, $viewer, Collection $members): array
    {
        $name = $message->user?->name ?? 'Former member';
        $parts = array_values(array_filter(explode(' ', trim($name))));
        $initials = mb_strtoupper(mb_substr($parts[0] ?? '?', 0, 1));
        if (count($parts) > 1) {
            $initials .= mb_strtoupper(mb_substr($parts[count($parts) - 1], 0, 1));
        }

        return [
            'id' => $message->id,
            'name' => $name,
            'initials' => $initials,
            'shade' => ($message->user_id ?? 0) % 3,
            'mine' => $message->user_id === $viewer->id,
            'body' => $message->body,
            'html' => $this->highlight($message->body, $viewer->name, $members),
            'mentionsMe' => $message->user_id !== $viewer->id
                && ($this->mentions($message->body, $viewer->name) || preg_match('/(^|\s)@(everyone|all)\b/i', $message->body)),
            'time' => $message->created_at->format('M j, g:i A'),
        ];
    }

    /**
     * The message as safe HTML with @mentions wrapped in a highlight.
     * The text is escaped first, so nothing a user types can run as code.
     */
    private function highlight(string $body, string $viewerName, Collection $members): string
    {
        $html = e($body);

        // Longest names first, so "@Ana Maria Cruz" is not cut short by "@Ana".
        $names = $members->pluck('name')->filter()->unique()->sortByDesc(fn ($n) => mb_strlen($n));

        // Each mention is first swapped for a marker, then the markers become
        // highlights at the end, so a short name is never matched inside a
        // longer one that was already handled.
        $markers = [];
        $index = 0;

        foreach ($names as $memberName) {
            $class = $memberName === $viewerName ? 'mention mention-me' : 'mention';
            $needle = '@'.e($memberName);
            $marker = "\x01".$index++."\x02";

            $markers[$marker] = '<span class="'.$class.'">'.$needle.'</span>';
            $html = str_ireplace($needle, $marker, $html);
        }

        $html = preg_replace('/(^|\s)@(everyone|all)\b/i', '$1<span class="mention mention-me">@$2</span>', $html);

        return strtr($html, $markers);
    }
}
