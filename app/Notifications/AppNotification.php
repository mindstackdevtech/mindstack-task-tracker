<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * One notification class for the whole app. It can be stored in the database
 * (shown under the bell), sent as an email, or both, depending on $channels.
 */
class AppNotification extends Notification
{
    public function __construct(
        public string $title,
        public string $body,
        public string $url,
        public array $channels = ['database'],
        public string $action = 'Open MindStack Task Tracker',
    ) {
    }

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /** What is saved for the in-app notification list. */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->greeting($this->title)
            ->line($this->body)
            ->action($this->action, $this->url)
            ->salutation('MindStack Task Tracker');
    }
}
