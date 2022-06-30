<?php

namespace App\Support\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

/**
 * Class Https.
 */
class Https
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $request->secure() && (App::environment() === 'local' || strpos($request->getBaseUrl(), 'test') !== false)) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
