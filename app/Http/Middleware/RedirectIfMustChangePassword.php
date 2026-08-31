<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfMustChangePassword
{
    /**
     * Force a client through the "change your password" page before they
     * can reach anything else, when their account was just created with
     * the shared default temporary password.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->must_change_password
            && !$request->routeIs('password.force-change')
            && !$request->routeIs('password.force-change.update')
            && !$request->routeIs('logout')
        ) {
            return redirect()->route('password.force-change');
        }

        return $next($request);
    }
}
