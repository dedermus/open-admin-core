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
        return Lang::has('auth.to_many_attempts')
            ? trans('auth.to_many_attempts')
            : 'To many attempts!';
    }
}
