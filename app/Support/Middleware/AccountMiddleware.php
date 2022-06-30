<?php

namespace App\Support\Middleware;

use App\Domain\Account\Models\Account;
use App\Domain\Company\Models\Company;
use App\Domain\User\Models\User;
use Closure;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AccountMiddleware
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
        $currentAccountId = $request->route('accountId');
        if (!auth()->user()->hasAccessToAccount($currentAccountId)) {
            abort(403);
        }

        $company = $request->route('company');
        $isCompanyOfCurrentAccount = !$company || !($company instanceof Company) || $company->account_id == $currentAccountId;
        abort_unless($isCompanyOfCurrentAccount, 403);

        $accounts = \Auth::user()->availableAccounts;
        \View::share('accounts', $accounts);

        $previousAccountCookieName = User::getPreviousAccountCookieName();
        $account = Account::find($currentAccountId);
        config(['app.accountId' => $currentAccountId]);
        View::share('accountName', $account ? $account->name : config('app.name'));
        View::share('userRole', current_general_role());
        $response = $next($request);
        if ($response instanceof BinaryFileResponse) {
            return $response;
        }
        if ($account) {
            return $response->withCookie(cookie()->forever($previousAccountCookieName, $currentAccountId));
        }

        return $response->withCookie(cookie()->forget($previousAccountCookieName));
    }
}
