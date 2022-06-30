<?php

namespace App\Support\Middleware;

use App\Domain\Company\Models\Company;
use App\Domain\Company\Models\CompanyRoleUser;
use App\Support\Helper\RouteHelper;

/**
 * Class ManagerMiddleware.
 */
class HasManagerAccessToCompanyMiddleware
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
        $company = $request->route('company');
        $user = current_user();

        if (!$user->hasRole('managers')) {
            return $next($request);
        }

        $hasPermission = CompanyRoleUser::query()
                ->where('user_id', $user->id)
                ->where('company_id', $company->id)
                ->count() > 0;
        if ($hasPermission) {
            return $next($request);
        }

        return redirect('/');
    }
}
