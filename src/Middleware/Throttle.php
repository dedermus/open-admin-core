<?php

namespace OpenAdminCore\Admin\Middleware;

use Closure;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\MessageBag;
use OpenAdminCore\Admin\Facades\Admin;

class Throttle
{
    protected string $loginView = 'admin::login';

    /**
     * Handle an incoming request.
     *
     * @param         $request
     * @param Closure $next
     *
     * @return Response|mixed
     */
    public function handle($request, Closure $next): mixed
    {
        // throttle this
        if (Admin::guard()->guest() && config('admin.auth.throttle_logins')) {
            $throttle_attempts = config('admin.auth.throttle_attempts', 5);
            if (RateLimiter::tooManyAttempts('login-tries-'.Admin::guardName(), $throttle_attempts)) {
                $errors = new MessageBag();
                $errors->add('attempts', $this->getToManyAttemptsMessage());

                return response()->view($this->loginView, ['errors'=>$errors], 429);
            }
        }

        return $next($request);
    }

    /**
     * @return array|Translator|Application|string
     */
    protected function getToManyAttemptsMessage(): Application|array|string|Translator
    {
        $key = 'login-tries-'.Admin::guardName();
        $secondsUntilReset = RateLimiter::availableIn($key);

        // Форматируем время
        $formattedTime = $this->formatTime($secondsUntilReset);

        return Lang::has('auth.to_many_attempts')
            ? trans('auth.to_many_attempts', ['time' => $formattedTime])
            : "Too many attempts! Please try again in {$formattedTime}.";
    }

    /**
     * Форматирует секунды в строку "X ч X мин X с"
     */
    protected function formatTime(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        $parts = [];

        if ($hours > 0) {
            $parts[] = "{$hours} " . trans_choice('auth.hours', $hours);
        }

        if ($minutes > 0) {
            $parts[] = "{$minutes} " . trans_choice('auth.minutes', $minutes);
        }

        if ($secs > 0 || empty($parts)) {
            $parts[] = "{$secs} " . trans_choice('auth.seconds', $secs);
        }

        return implode(' ', $parts);
    }
}
