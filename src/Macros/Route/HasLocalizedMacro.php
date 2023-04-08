<?php

namespace CodeZero\LocalizedRoutes\Macros\Route;

use CodeZero\LocalizedRoutes\RouteHelper;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

class HasLocalizedMacro
{
    /**
     * Register the macro.
     *
     * @return void
     */
    public static function register()
    {
        Route::macro('hasLocalized', function (string $name, ?string $locale = null) {
            return App::make(RouteHelper::class)->hasLocalized($name, $locale);
        });
    }
}
