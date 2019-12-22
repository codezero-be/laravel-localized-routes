<?php

namespace CodeZero\LocalizedRoutes\Macros;

use CodeZero\LocalizedRoutes\ProvidesRouteParameters;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

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
            $parameters = $parameters ?: Route::current()->parameters();
            $model = Collection::make($parameters)->first();

            if ($model instanceof ProvidesRouteParameters) {
                $parameters = $model->getRouteParameters($locale);
            }

            if (is_callable($parameters)) {
                $parameters = $parameters($locale);
            }

            return route(Route::currentRouteName(), $parameters, $absolute, $locale);
        });
    }
}
