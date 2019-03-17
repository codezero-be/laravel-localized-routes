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

            $locales = Config::get('localized-routes.supported-locales', []);
            $omitPrefix = Config::get('localized-routes.omit_url_prefix_for_locale');

            foreach ($locales as $locale) {
                // Change the current locale so we can
                // use it in the callback, for example
                // to register translated route URI's.
                App::setLocale($locale);

                // Create a new route and prepend
                // the locale to the route name.
                $route = Route::name("{$locale}.");

                // Prefix the URL unless the locale
                // is configured to be omitted.
                if ($locale !== $omitPrefix) {
                    $route->prefix($locale);
                }

                $route->group($callback);
            }

            // Restore the original locale.
            App::setLocale($currentLocale);
        });
    }
}
