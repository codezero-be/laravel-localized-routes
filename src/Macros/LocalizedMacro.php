<?php

namespace CodeZero\LocalizedRoutes\Macros;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class LocalizedMacro
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

            $locales = $options['supported_locales'] ?? Config::get('localized-routes.supported_locales', []);
            $omitPrefix = $options['omitted_locale'] ?? Config::get('localized-routes.omitted_locale');

            if (count($locales) === 0) {
                return;
            }

            $firstValue = array_values($locales)[0];
            $usingDomains = Str::contains($firstValue, '.');
            $usingCustomSlugs = ! $usingDomains && ! is_numeric(key($locales));

            // Move the omitted locale to the end of the array
            // to avoid root placeholders catching existing slugs.
            // https://github.com/codezero-be/laravel-localized-routes/issues/28
            if ($omitPrefix && ! $usingDomains) {
                if ($usingCustomSlugs) {
                    $omitSlug = $locales[$omitPrefix];
                    unset($locales[$omitPrefix]);
                    $locales[$omitPrefix] = $omitSlug;
                } else {
                    $locales = array_filter($locales, function ($locale) use ($omitPrefix) {
                        return $locale !== $omitPrefix;
                    });
                    $locales[] = $omitPrefix;
                }
            }

            foreach ($locales as $locale => $domainOrSlug) {
                // Allow supported locales to be a
                // simple array of locales or an
                // array of ['locale' => 'domain']
                if ( ! $usingDomains && ! $usingCustomSlugs) {
                    $locale = $domainOrSlug;
                    $domainOrSlug = null;
                }

                // Change the current locale so we can
                // use it in the callback, for example
                // to register translated route URI's.
                App::setLocale($locale);

                // Prepend the locale to the route name
                // and set a custom attribute so the middleware
                // can find it to set the correct app locale.
                $localeRouteAction = Config::get('localized-routes.route_action');
                $attributes = [
                    'as' => "{$locale}.",
                    $localeRouteAction => $locale
                ];

                // Add a custom domain route group
                // if a domain is configured.
                if ($usingDomains) {
                    $attributes['domain'] = $domainOrSlug;
                }

                // Map the locale string to a prefix.
                $prefix = $usingCustomSlugs ? $domainOrSlug : $locale;

                // Prefix the URL unless the locale
                // is configured to be omitted.
                if ( ! $usingDomains && $locale !== $omitPrefix) {
                    $attributes['prefix'] = $prefix;
                }

                // Execute the callback inside route group
                Route::group($attributes, $callback);
            }

            // Restore the original locale.
            App::setLocale($currentLocale);
        });
    }
}
