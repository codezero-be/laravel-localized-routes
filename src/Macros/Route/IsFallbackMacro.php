<?php

namespace CodeZero\LocalizedRoutes\Macros\Route;

use Illuminate\Support\Facades\Route;

class IsFallbackMacro
{
    /**
     * Register the macro.
     *
     * @return void
     */
    public static function register()
    {
        Route::macro('isFallback', function () {
            return Route::current() && Route::current()->isFallback;
        });
    }
}
