<?php

namespace OpenAdminCore\Admin\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    protected string $token;

    /**
     * Create a new notification instance.
     *
     * @param string $token
     * @return void
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);

        $expireMinutes = config('admin.auth.password_reset.expire', 60);

        return (new MailMessage)
            ->subject(__('admin::admin.password_reset.subject'))
            ->line(__('admin::admin.password_reset.intro'))
            ->action(__('admin::admin.password_reset.action'), $resetUrl)
            ->line(__('admin::admin.password_reset.expire', ['minutes' => $expireMinutes]))
            ->line(__('admin::admin.password_reset.outro'));
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param mixed $notifiable
     * @return string
     */
    protected function resetUrl($notifiable): string
    {
        return route('admin.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
