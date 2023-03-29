<?php

namespace CodeZero\LocalizedRoutes\Macros\Route;

use CodeZero\LocalizedRoutes\Facades\LocaleConfig;
use Illuminate\Support\Collection;
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
        Route::macro('isLocalized', function ($patterns = null, $locales = '*') {
            if ($patterns === null) {
                $routeAction = LocaleConfig::getRouteAction();
                $route = Route::current();

                return $route && $route->getAction($routeAction) !== null;
            }

            $locales = Collection::make($locales);
            $names = Collection::make();

            Collection::make($patterns)->each(function ($name) use ($locales, $names) {
                $locales->each(function ($locale) use ($name, $names) {
                    $names->push($locale . '.' . $name);
                });
            });

            return Route::is($names->all());
        });
    }
}
