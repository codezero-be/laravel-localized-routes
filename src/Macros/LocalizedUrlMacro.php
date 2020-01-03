<?php

namespace CodeZero\LocalizedRoutes\Macros;

use CodeZero\LocalizedRoutes\ProvidesRouteParameters;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class LocalizedUrlMacro
{
    /**
     * Register the macro.
     *
     * @return void
     */
    public static function register()
    {
        Route::macro('localizedUrl', function ($locale = null, $parameters = null, $absolute = true) {
            if (( ! $route = Route::current()) || ! $route->getAction('localized-routes-locale')) {
                return URL::current();
            }

            $parameters = $parameters ?: $route->parameters();
            $model = Collection::make($parameters)->first();

            if ($model instanceof ProvidesRouteParameters) {
                $parameters = $model->getRouteParameters($locale);
            }

            if (is_callable($parameters)) {
                $parameters = $parameters($locale);
            }

            return route($route->getName(), $parameters, $absolute, $locale);
        });
    }
}
