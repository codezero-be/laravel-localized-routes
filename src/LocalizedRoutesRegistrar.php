<?php

namespace CodeZero\LocalizedRoutes;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class LocalizedRoutesRegistrar
{
    /**
     * Register routes for every configured locale.
     *
     * @param Closure $closure
     * @param array $options
     *
     * @return void
     */
    public function register($closure, $options = [])
    {
        $locales = $options['supported_locales'] ?? Config::get('localized-routes.supported_locales', []);
        $omitPrefix = $options['omitted_locale'] ?? Config::get('localized-routes.omitted_locale');
        $localeRouteAction = Config::get('localized-routes.route_action');

        if (count($locales) === 0) {
            return;
        }

        $firstValue = array_values($locales)[0];
        $usingDomains = Str::contains($firstValue, '.');
        $usingCustomSlugs = ! $usingDomains && ! is_numeric(key($locales));

        // Move the omitted locale to the end of the array to avoid
        // root parameter placeholders catching hard-coded slugs.
        $locales = $this->moveOmittedLocaleToEnd($locales, $omitPrefix, $usingDomains, $usingCustomSlugs);

        // Remember the current locale, so we can
        // change it during route registration.
        $currentLocale = App::getLocale();

        foreach ($locales as $locale => $domainOrSlug) {
            // If the locale key is numeric, we have a simple array of locales.
            // In this case, the locale is the same as the slug.
            if ( ! $usingDomains && ! $usingCustomSlugs) {
                $locale = $domainOrSlug;
            }

            // Prepend the locale to the route names and set
            // a custom route action, so the middleware can
            // find it to set the correct app locale.
            $attributes = [
                'as' => "{$locale}.",
                $localeRouteAction => $locale,
            ];

            // Add a custom domain to the route group
            // when custom domains are configured.
            if ($usingDomains) {
                $attributes['domain'] = $domainOrSlug;
            }

            // Add a URL prefix to the route group, unless
            // the locale is configured to be omitted.
            if ( ! $usingDomains && $locale !== $omitPrefix) {
                $attributes['prefix'] = $domainOrSlug;
            }

            // Temporarily change the active locale, so any
            // translations made in the routes closure are
            // automatically in the correct language.
            App::setLocale($locale);

            // Register the route group.
            Route::group($attributes, $closure);
        }

        // Restore the original locale.
        App::setLocale($currentLocale);
    }

    /**
     * Move the omitted locale to the end of the locales array.
     *
     * @param array $locales
     * @param string|null $omitPrefix
     * @param bool $usingDomains
     * @param bool $usingCustomSlugs
     *
     * @return array
     */
    protected function moveOmittedLocaleToEnd($locales, $omitPrefix, $usingDomains, $usingCustomSlugs)
    {
        if ( ! $omitPrefix || $usingDomains) {
            return $locales;
        }

        // When using custom slugs, the locales are the array keys.
        // Remove it from the array and add it back on to the end.
        if ($usingCustomSlugs) {
            $omitSlug = $locales[$omitPrefix];
            unset($locales[$omitPrefix]);
            $locales[$omitPrefix] = $omitSlug;

            return $locales;
        }

        // When not using custom slugs, the array keys are numeric.
        // Filter out the omitted locale and then add it back to the end.
        $locales = array_filter($locales, function ($locale) use ($omitPrefix) {
            return $locale !== $omitPrefix;
        });

        $locales[] = $omitPrefix;

        return $locales;
    }
}
