<?php

namespace OpenAdminCore\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Guest
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $guard = config('admin.auth.guard', 'admin');

        if (Auth::guard($guard)->check()) {
            return redirect(config('admin.route.prefix', 'admin'));
        }

        return $next($request);
    }
}