<?php

namespace CodeZero\LocalizedRoutes\Macros\Route;

use CodeZero\LocalizedRoutes\LocalizedUrlGenerator;
use Illuminate\Support\Facades\App;
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
        Route::macro('localizedUrl', function ($locale = null, $parameters = null, $absolute = true, $keepQuery = true) {
            return App::make(LocalizedUrlGenerator::class)->generateFromRequest($locale, $parameters, $absolute, $keepQuery);
        });
    }
}
