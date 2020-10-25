<?php

namespace CodeZero\LocalizedRoutes\Macros;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

class LocalizedHasMacro
{
    /**
     * Register the macro.
     *
     * @return void
     */
    public static function register()
    {
        Route::macro('localizedHas', function ($name, $locale = null) {
            $locale = $locale ?? App::getLocale();
            if (! $this->routes->hasNamedRoute($locale . ".{$name}")) {
                return false;
            }

            return true;
        });
    }
}
