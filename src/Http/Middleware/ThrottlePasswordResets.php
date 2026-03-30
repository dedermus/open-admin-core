<?php

namespace OpenAdminCore\Admin\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ThrottlePasswordResets
{
    /**
     * The rate limiter instance.
     *
     * @var RateLimiter
     */
    protected RateLimiter $limiter;

    /**
     * Create a new middleware instance.
     *
     * @param RateLimiter $limiter
     * @return void
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $maxAttempts = config('auth.password_reset.throttle', 3);
        $decayMinutes = config('admin.auth.password_reset.throttle_decay_minutes', 60);

        $key = 'admin-password-reset:' . $request->ip();

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $seconds = $this->limiter->availableIn($key);

            Log::channel('password_reset')->warning('Too many password reset attempts', [
                'ip' => $request->ip(),
                'seconds_remaining' => $seconds,
                'user_agent' => $request->userAgent(),
            ]);

            return back()->withErrors([
                'credential' => __('admin.password_reset.throttle', ['seconds' => $seconds]),
            ])->withInput();
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        return $next($request);
    }
}
