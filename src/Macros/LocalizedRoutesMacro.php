<?php

namespace CodeZero\LocalizedRoutes\Macros;

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
            $currentLocale = app()->getLocale();

            $locales = config('localized-routes.supported-locales', []);
            $omitPrefix = config('localized-routes.omit_url_prefix_for_locale');

            foreach ($locales as $locale => $domain) {
                // Allow supported locales to be a
                // simple array of locales or an
                // array of ['locale' => 'domain']
                if (is_numeric($locale)) {
                    $locale = $domain;
                    $domain = null;
                }

                // Change the current locale so we can
                // use it in the callback, for example
                // to register translated route URI's.
                app()->setLocale($locale);

                // Create a new route and prepend
                // the locale to the route name.
                $route = Route::name("{$locale}.");

                // Add a custom domain route group
                // if a domain is configured.
                if ($domain !== null) {
                    $route->domain($domain);
                }

                // Prefix the URL unless the locale
                // is configured to be omitted.
                if ($domain === null && $locale !== $omitPrefix) {
                    $route->prefix($locale);
                }

                $route->group($callback);
            }

            // Restore the original locale.
            app()->setLocale($currentLocale);
        });
    }
}
