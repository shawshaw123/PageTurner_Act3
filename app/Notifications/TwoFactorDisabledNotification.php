<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorDisabledNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Two-Factor Authentication Disabled')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Two-factor authentication has been disabled on your PageTurner account.')
            ->line('Your account is now less secure. We recommend re-enabling 2FA.')
            ->line('If you did not make this change, please contact support immediately.')
            ->action('View Security Settings', url('/two-factor'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => '2fa_disabled',
            'message' => 'Two-factor authentication has been disabled on your account.',
        ];
    }
}
