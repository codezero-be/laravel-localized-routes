<?php

namespace CodeZero\LocalizedRoutes\Macros\Route;

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
        Route::macro('hasLocalized', function ($name, $locale = null) {
            $locale = $locale ?: App::getLocale();

            return $this->routes->hasNamedRoute("{$locale}.{$name}");
        });
    }
}
