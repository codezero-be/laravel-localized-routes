<?php

namespace CodeZero\LocalizedRoutes\Macros\Route;

use CodeZero\LocalizedRoutes\RouteHelper;
use Illuminate\Support\Facades\App;
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
            return App::make(RouteHelper::class)->isFallback();
        });
    }
}
