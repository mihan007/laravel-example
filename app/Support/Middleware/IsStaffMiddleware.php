<?php

namespace App\Support\Middleware;

/**
 * Class ManagerMiddleware.
 */
class IsStaffMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        if (current_user()->is_staff) {
            return $next($request);
        }

        abort(403);
    }
}
