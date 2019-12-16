<?php

namespace CodeZero\LocalizedRoutes\Middleware;

use Closure;
use CodeZero\LocalizedRoutes\LocaleHandler;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // This package sets a custom route attribute for the locale.
        // If it is present, use this as the locale.
        $locale = $request->route()->getAction('localized-routes-locale');

        App::make(LocaleHandler::class)->handleLocale($locale);

        return $next($request);
    }
}
