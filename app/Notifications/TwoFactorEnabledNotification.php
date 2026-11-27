<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorEnabledNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Two-Factor Authentication Enabled')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Two-factor authentication has been enabled on your PageTurner account.')
            ->line('You will now need to enter a verification code each time you log in.')
            ->line('If you did not make this change, please contact support immediately.')
            ->action('View Security Settings', url('/two-factor'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => '2fa_enabled',
            'message' => 'Two-factor authentication has been enabled on your account.',
        ];
    }
}
