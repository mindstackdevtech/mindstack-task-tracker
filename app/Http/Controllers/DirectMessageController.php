<?php

namespace App\Http\Controllers;

use App\Models\DirectMessage;
use App\Models\Project;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Personal messages between two people.
 * You can only write to someone who shares at least one project with you.
 */
class DirectMessageController extends Controller
{
    /** Your conversations, plus teammates you have not written to yet. */
    public function index(Request $request)
    {
        $me = $request->user();
        $contactIds = $this->contactIds($me);

        // Newest message and unread count for each person you have talked to.
        $threads = [];
        $recent = DirectMessage::where('sender_id', $me->id)
            ->orWhere('recipient_id', $me->id)
            ->latest('id')
            ->limit(1000)
            ->get();

        foreach ($recent as $message) {
            $otherId = $message->sender_id === $me->id ? $message->recipient_id : $message->sender_id;
            $threads[$otherId] ??= ['last' => $message, 'unread' => 0];

            if ($message->recipient_id === $me->id && $message->read_at === null) {
                $threads[$otherId]['unread']++;
            }
        }

        $people = User::whereIn('id', $contactIds->merge(array_keys($threads))->unique())
            ->orderBy('name')
            ->get()
            ->map(fn (User $person) => [
                'user' => $person,
                'last' => $threads[$person->id]['last'] ?? null,
                'unread' => $threads[$person->id]['unread'] ?? 0,
            ])
            // People you have talked to first (newest on top), then everyone else by name.
            ->sortByDesc(fn (array $row) => $row['last']?->id ?? 0)
            ->values();

        return view('messages.index', ['people' => $people, 'me' => $me]);
    }

    /** One conversation. Opening it marks their messages as read. */
    public function show(Request $request, User $user)
    {
        $me = $request->user();
        $canWrite = $this->authorizeThread($me, $user);

        $this->markRead($me, $user);

        $messages = DirectMessage::between($me->id, $user->id)
            ->latest('id')
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        return view('messages.show', [
            'other' => $user,
            'canWrite' => $canWrite,
            'messages' => $messages->map(fn (DirectMessage $m) => $this->present($m, $me, $user)),
            'lastId' => (int) ($messages->last()->id ?? 0),
        ]);
    }

    /** New messages since the last one the page has (the page asks every few seconds). */
    public function feed(Request $request, User $user)
    {
        $me = $request->user();
        $this->authorizeThread($me, $user);

        $messages = DirectMessage::between($me->id, $user->id)
            ->where('id', '>', (int) $request->query('after', 0))
            ->orderBy('id')
            ->limit(100)
            ->get();

        $this->markRead($me, $user);

        return response()->json([
            'messages' => $messages->map(fn (DirectMessage $m) => $this->present($m, $me, $user))->values(),
        ]);
    }

    public function store(Request $request, User $user)
    {
        $me = $request->user();

        abort_unless($this->authorizeThread($me, $user), 403, 'You can only message people who share a project with you.');

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = DirectMessage::create([
            'sender_id' => $me->id,
            'recipient_id' => $user->id,
            'body' => $data['body'],
        ]);

        // One bell notification per conversation until they read it,
        // not one for every message.
        $url = route('messages.show', $me);
        $alreadyTold = $user->unreadNotifications
            ->contains(fn ($notification) => ($notification->data['url'] ?? '') === $url);

        if (! $alreadyTold) {
            $user->notify(new AppNotification(
                'New message from '.$me->name,
                Str::limit($data['body'], 120),
                $url,
                ['database'],
            ));
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $this->present($message, $me, $user)]);
        }

        return redirect()->route('messages.show', $user);
    }

    /** Ids of everyone who shares at least one project with this user. */
    private function contactIds(User $me): Collection
    {
        return DB::table('project_user')
            ->whereIn('project_id', Project::idsFor($me))
            ->where('user_id', '!=', $me->id)
            ->distinct()
            ->pluck('user_id');
    }

    /**
     * Stops with "403" unless you may open this conversation.
     * Returns true when you may also write (you still share a project);
     * false means an old conversation you can read but not add to.
     */
    private function authorizeThread(User $me, User $other): bool
    {
        abort_if($me->id === $other->id, 404);

        if ($this->contactIds($me)->contains($other->id)) {
            return true;
        }

        abort_unless(DirectMessage::between($me->id, $other->id)->exists(), 403, 'You can only message people who share a project with you.');

        return false;
    }

    private function markRead(User $me, User $other): void
    {
        DirectMessage::where('sender_id', $other->id)
            ->where('recipient_id', $me->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Clear the bell notification for this conversation too.
        $url = route('messages.show', $other);
        $me->unreadNotifications
            ->filter(fn ($notification) => ($notification->data['url'] ?? '') === $url)
            ->each->markAsRead();
    }

    /** Same shape the team chat uses, so both share public/js/chat.js. */
    private function present(DirectMessage $message, User $me, User $other): array
    {
        $author = $message->sender_id === $me->id ? $me : $other;
        $parts = array_values(array_filter(explode(' ', trim($author->name))));
        $initials = mb_strtoupper(mb_substr($parts[0] ?? '?', 0, 1));
        if (count($parts) > 1) {
            $initials .= mb_strtoupper(mb_substr($parts[count($parts) - 1], 0, 1));
        }

        return [
            'id' => $message->id,
            'name' => $author->name,
            'initials' => $initials,
            'shade' => $author->id % 3,
            'mine' => $message->sender_id === $me->id,
            'body' => $message->body,
            'time' => $message->created_at->format('M j, g:i A'),
        ];
    }
}
