<?php

namespace CodeZero\LocalizedRoutes\Middleware;

use Closure;
use \Illuminate\Support\Facades\App;

class LocalizedRouteLocaleHandler
{
    /**
     * Set language for localized route
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $action = $request->route()->getAction();

        if ($action['localized-routes-locale']) {
            App::setLocale($action['localized-routes-locale']);
        }

        return $next($request);
    }
}
