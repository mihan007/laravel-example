<?php

namespace App\Support\Middleware;

use App\Domain\Company\Models\Company;
use Closure;

class CompanyAccountMiddleware
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
        if (is_object($request->route('company'))) {
            $id = $request->route('company')->id;
        } else {
            $id = $request->route('id') ?? $request->route('company');
        }
        $accountId = $request->route('accountId');

        if ($id && $accountId) {
            if (! Company::where('id', $id)->where('account_id', $accountId)->exists()) {
                abort(404);
            }
        }

        return $next($request);
    }
}
