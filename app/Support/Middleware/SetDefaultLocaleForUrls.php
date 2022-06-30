<?php

namespace App\Support\Middleware;

use App\Domain\User\Models\User;
use Closure;
use Illuminate\Support\Facades\URL;

class SetDefaultLocaleForUrls
{
    public function handle($request, Closure $next)
    {
        $currentAccountId = $request->route('accountId');
        if (auth()->user() && auth()->user()->account) {
            URL::defaults(['accountId' => $currentAccountId ? $currentAccountId : User::current()->account->id]);
        }

        return $next($request);
    }
}
