<?php

namespace App\Support\Middleware;

use Closure;

class HasUserAccessToCompanyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $company = $request->route('company');
        $user = current_user();

        if ($user->isAdmin || $user->isSuperAdmin) {
            return $next($request);
        }

        if ($user->company_id === $company->id) {
            return $next($request);
        }

        $ids = $user->getCompanyForUser($user->id)->pluck('id')->toArray();

        if (in_array($company->id, $ids)) {
            return $next($request);
        }

        abort(404);
    }
}
