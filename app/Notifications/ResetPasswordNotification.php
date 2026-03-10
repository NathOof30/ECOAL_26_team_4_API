<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Password')
            ->line('Use the token below to reset your password via the API.')
            ->line('Token: '.$this->token)
            ->line('Email: '.$notifiable->email)
            ->line('Send both values to POST /api/reset-password together with the new password.');
    }
}
