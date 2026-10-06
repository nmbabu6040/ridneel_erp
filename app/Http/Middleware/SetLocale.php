<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (!$locale && $request->user()) {
            $locale = $request->user()->preferred_locale;
        }

        $locale = in_array($locale, ['bn', 'en'], true) ? $locale : 'bn';

        app()->setLocale($locale);
        $request->session()->put('locale', $locale);

        return $next($request);
    }
}
