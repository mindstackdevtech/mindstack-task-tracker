<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Sends notifications. A failed email (wrong mail settings, no internet)
 * is written to the log instead of breaking the page.
 */
class Notifier
{
    /** In-app notification plus an email to a registered user. */
    public static function send(User $user, string $title, string $body, string $url): void
    {
        $user->notify(new AppNotification($title, $body, $url, ['database']));

        static::attempt(fn () => $user->notify(new AppNotification($title, $body, $url, ['mail'])));
    }

    /** Email only, to an address that may not have an account yet. */
    public static function mailTo(string $email, string $title, string $body, string $url, string $action): bool
    {
        return static::attempt(fn () => Notification::route('mail', $email)
            ->notify(new AppNotification($title, $body, $url, ['mail'], $action)));
    }

    private static function attempt(callable $send): bool
    {
        try {
            $send();

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
