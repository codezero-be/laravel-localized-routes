<?php

namespace CodeZero\LocalizedRoutes\Macros;

use CodeZero\LocalizedRoutes\Middleware\SetLocale;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class LocalizedRoutesMacro
{
    /**
     * Register the macro.
     *
     * @return void
     */
    public static function register()
    {
        Route::macro('localized', function ($callback, $options = []) {
            // Remember the current locale so we can
            // change it during route registration.
            $currentLocale = App::getLocale();

            $locales = $options['supported-locales']
                                ?? Config::get('localized-routes.supported-locales', []);
            $omitPrefix = $options['omit_url_prefix_for_locale']
                                ?? Config::get('localized-routes.omit_url_prefix_for_locale');
            $setMiddleware = $options['use_locale_middleware']
                                ?? Config::get('localized-routes.use_locale_middleware', false);

            $notUsingDomains = is_numeric(array_key_first($locales));

            if ($omitPrefix && $notUsingDomains) {
                // Move the omitted locale to the end of the array
                // to avoid root placeholders catching existing slugs.
                // https://github.com/codezero-be/laravel-localized-routes/issues/28
                $locales = array_filter($locales, function ($locale) use ($omitPrefix) {
                    return $locale !== $omitPrefix;
                });
                array_push($locales, $omitPrefix);
            }

            foreach ($locales as $locale => $domain) {
                // Allow supported locales to be a
                // simple array of locales or an
                // array of ['locale' => 'domain']
                if ($notUsingDomains) {
                    $locale = $domain;
                    $domain = null;
                }

                // Change the current locale so we can
                // use it in the callback, for example
                // to register translated route URI's.
                App::setLocale($locale);

                // Prepend the locale to the route name
                // and set a custom attribute so the middleware
                // can find it to set the correct app locale.
                $attributes = [
                    'as' => "{$locale}.",
                    'localized-routes-locale' => $locale
                ];

                // Add a custom domain route group
                // if a domain is configured.
                if ($domain !== null) {
                    $attributes['domain'] = $domain;
                }

                // Map the locale string to a prefix.
                $prefix = data_get(Config::get('localized-routes.custom_prefixes'), $locale, $locale);

                // Prefix the URL unless the locale
                // is configured to be omitted.
                if ($domain === null && $locale !== $omitPrefix) {
                    $attributes['prefix'] = $prefix;
                }

                if ($setMiddleware) {
                    $attributes['middleware'] = [SetLocale::class];
                }

                // Execute the callback inside route group
                Route::group($attributes, $callback);
            }

            // Restore the original locale.
            App::setLocale($currentLocale);
        });
    }
}
