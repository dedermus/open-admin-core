<?php

namespace OpenAdminCore\Admin\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetSuccess extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('admin.password_reset.success_subject', ['app_name' => config('app.name')]))
            ->greeting(__('admin.password_reset.hello', ['name' => $notifiable->username]))
            ->line(__('admin.password_reset.success_line_1'))
            ->line(__('admin.password_reset.success_line_2'))
            ->action(__('admin.password_reset.login_action'), url('/admin/auth/login'))
            ->line(__('admin.password_reset.thank_you'));
    }
}
