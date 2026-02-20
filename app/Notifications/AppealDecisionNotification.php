<?php

namespace App\Notifications;

use App\Models\Appeal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppealDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(public Appeal $appeal) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $outcome = $this->appeal->status->value;

        return (new MailMessage)
            ->subject('Appeal Decision')
            ->line("Your appeal has been {$outcome}.");
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'appeal_id' => $this->appeal->id,
            'status' => $this->appeal->status->value,
        ];
    }
}
