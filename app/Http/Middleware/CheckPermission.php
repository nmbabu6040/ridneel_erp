<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Usage in routes: ->middleware('permission:users.view')
     * Multiple keys can be passed separated by "|" — user needs at least one.
     */
    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if (!$user->isActive()) {
            auth()->logout();
            abort(403, __('app.account_suspended'));
        }

        $keys = explode('|', $permissions);

        foreach ($keys as $key) {
            if ($user->hasPermission($key)) {
                return $next($request);
            }
        }

        abort(403, __('app.no_permission'));
    }
}
