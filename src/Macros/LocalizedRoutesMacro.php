<?php

namespace CodeZero\LocalizedRoutes\Macros;

use App;
use Config;
use Route;

class LocalizedRoutesMacro
{
    /**
     * Register the macro.
     *
     * @return void
     */
    public static function register()
    {
        Route::macro('localized', function ($callback) {
            // Remember the current locale so we can
            // change it during route registration.
            $currentLocale = App::getLocale();

            $locales = Config::get('app.locales', []);

            foreach ($locales as $locale) {
                // Change the current locale so we can
                // use it in the callback, for example
                // to register translated route URI's.
                App::setLocale($locale);

                // Wrap the localized routes in a group and prepend
                // the locale to the URI and the route name.
                Route::prefix($locale)->name("{$locale}.")->group($callback);
            }

            // Restore the original locale.
            App::setLocale($currentLocale);
        });
    }
}
