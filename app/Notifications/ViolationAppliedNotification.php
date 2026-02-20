<?php

namespace App\Notifications;

use App\Models\Violation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ViolationAppliedNotification extends Notification
{
    use Queueable;

    public function __construct(public Violation $violation, public bool $pendingCosign = false) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Community Guidelines Violation');

        if ($this->pendingCosign) {
            $message->line('A potential violation has been flagged on your account and is pending review.');
        } else {
            $message->line('A violation has been confirmed on your account.')
                ->line("Consequence: {$this->violation->consequence_applied->value}");
        }

        return $message;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'violation_id' => $this->violation->id,
            'pending_cosign' => $this->pendingCosign,
        ];
    }
}
