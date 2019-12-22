<?php

namespace CodeZero\LocalizedRoutes\Macros;

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
            return route(Route::currentRouteName(), $parameters ?: Route::current()->parameters(), $absolute, $locale);
        });
    }
}
