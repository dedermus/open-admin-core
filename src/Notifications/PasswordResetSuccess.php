<?php

namespace OpenAdminCore\Admin\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetSuccess extends Notification
{
    use Queueable;

    protected $ip;
    protected $userAgent;
    protected $resetTime;
    protected $browserInfo;

    /**
     * Create a new notification instance.
     *
     * @param string|null $ip
     * @param string|null $userAgent
     */
    public function __construct($ip = null, $userAgent = null)
    {
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->resetTime = now();
        $this->browserInfo = $this->parseUserAgent($userAgent);
    }

    /**
     * Parse User-Agent string to get browser, OS and device info.
     *
     * @param string|null $userAgent
     * @return array
     */
    protected function parseUserAgent($userAgent)
    {
        if (empty($userAgent)) {
            return [
                'browser' => __('admin.password_reset.unknown'),
                'os' => __('admin.password_reset.unknown'),
                'device' => __('admin.password_reset.unknown'),
                'full' => __('admin.password_reset.unknown')
            ];
        }

        // Простой парсинг User-Agent
        $info = [];

        // Определение браузера
        if (strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Edg') === false) {
            $info['browser'] = 'Google Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $info['browser'] = 'Mozilla Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
            $info['browser'] = 'Safari';
        } elseif (strpos($userAgent, 'Edg') !== false) {
            $info['browser'] = 'Microsoft Edge';
        } elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) {
            $info['browser'] = 'Opera';
        } elseif (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) {
            $info['browser'] = 'Internet Explorer';
        } else {
            $info['browser'] = __('admin.password_reset.unknown_browser');
        }

        // Определение ОС
        if (strpos($userAgent, 'Windows') !== false) {
            if (strpos($userAgent, 'Windows NT 10.0') !== false) $info['os'] = 'Windows 10/11';
            elseif (strpos($userAgent, 'Windows NT 6.3') !== false) $info['os'] = 'Windows 8.1';
            elseif (strpos($userAgent, 'Windows NT 6.2') !== false) $info['os'] = 'Windows 8';
            elseif (strpos($userAgent, 'Windows NT 6.1') !== false) $info['os'] = 'Windows 7';
            else $info['os'] = 'Windows';
        } elseif (strpos($userAgent, 'Mac') !== false) {
            $info['os'] = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $info['os'] = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $info['os'] = 'Android';
        } elseif (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
            $info['os'] = 'iOS';
        } else {
            $info['os'] = __('admin.password_reset.unknown_os');
        }

        // Определение устройства
        if (strpos($userAgent, 'Mobile') !== false || strpos($userAgent, 'Android') !== false) {
            $info['device'] = __('admin.password_reset.mobile_device');
        } elseif (strpos($userAgent, 'iPad') !== false) {
            $info['device'] = __('admin.password_reset.tablet_device');
        } else {
            $info['device'] = __('admin.password_reset.desktop_device');
        }

        $info['full'] = $userAgent;

        return $info;
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
        $formattedTime = $this->resetTime->format('d.m.Y в H:i:s');
        $timezone = config('app.timezone', 'UTC');

        return (new MailMessage)
            ->subject(__('admin.password_reset.success_subject', ['app_name' => config('app.name')]))
            ->greeting(__('admin.password_reset.hello', ['name' => $notifiable->username]))
            ->line(__('admin.password_reset.success_line_1'))
            ->line('')
            ->line(__('admin.password_reset.security_info_header'))
            ->line(__('admin.password_reset.password_changed_info', [
                'time' => $formattedTime,
                'timezone' => $timezone
            ]))
            ->line(__('admin.password_reset.ip_address', ['ip' => $this->ip ?? __('admin.password_reset.ip_unknown')]))
            ->line(__('admin.password_reset.browser_info', [
                'browser' => $this->browserInfo['browser'],
                'os' => $this->browserInfo['os'],
                'device' => $this->browserInfo['device']
            ]))
            ->line('')
            ->line(__('admin.password_reset.success_line_2'))
            ->action(__('admin.password_reset.login_action'), url('/admin/auth/login'))
            ->line('')
            ->line(__('admin.password_reset.thank_you'))
            ->salutation(__('admin.password_reset.salutation', ['app_name' => config('app.name')]));
    }
}
