<?php

namespace CodeZero\LocalizedRoutes\Macros;

use CodeZero\LocalizedRoutes\LocalizedUrlGenerator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

class IsLocalizedMacro
{
    /**
     * Register the macro.
     *
     * @return void
     */
    public static function register()
    {
        Route::macro('isLocalized', function () {
            return App::make(LocalizedUrlGenerator::class)->isLocalized();
        });
    }
}
