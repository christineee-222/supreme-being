<?php

namespace App\Notifications;

use App\Models\ModeratorApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModeratorApplicationDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(public ModeratorApplication $application) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $outcome = $this->application->status->value;

        return (new MailMessage)
            ->subject('Moderator Application Decision')
            ->line("Your moderator application has been {$outcome}.");
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'status' => $this->application->status->value,
        ];
    }
}
