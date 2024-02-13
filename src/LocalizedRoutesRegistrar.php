<?php

namespace CodeZero\LocalizedRoutes;

use Closure;
use CodeZero\LocalizedRoutes\Facades\LocaleConfig;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

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
    public function register(Closure $closure, array $options = []): void
    {
        $locales = $options['supported_locales'] ?? LocaleConfig::getSupportedLocales();
        $omittedLocale = $options['omitted_locale'] ?? LocaleConfig::getOmittedLocale();

        if (count($locales) === 0) {
            return;
        }

        $localeRouteAction = LocaleConfig::getRouteAction();
        $usingDomains = LocaleConfig::hasCustomDomains();
        $usingCustomSlugs = LocaleConfig::hasCustomSlugs();

        // Move the omitted locale to the end of the array to avoid
        // root parameter placeholders catching hard-coded slugs.
        $locales = $this->moveOmittedLocaleToEnd($locales, $omittedLocale, $usingDomains, $usingCustomSlugs);

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
            if ( ! $usingDomains && $locale !== $omittedLocale) {
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
     * @param string|null $omittedLocale
     * @param bool $usingDomains
     * @param bool $usingCustomSlugs
     *
     * @return array
     */
    protected function moveOmittedLocaleToEnd(array $locales, ?string $omittedLocale, bool $usingDomains, bool $usingCustomSlugs): array
    {
        if ( ! $omittedLocale || $usingDomains) {
            return $locales;
        }

        // When using custom slugs, the locales are the array keys.
        // Remove the omitted locale from the array
        // and add it back on to the end.
        if ($usingCustomSlugs) {
            $omitSlug = $locales[$omittedLocale];
            unset($locales[$omittedLocale]);
            $locales[$omittedLocale] = $omitSlug;

            return $locales;
        }

        // When not using custom slugs or domains, the array keys are numeric.
        // Filter out the omitted locale and then add it back to the end.
        $locales = array_filter($locales, function ($locale) use ($omittedLocale) {
            return $locale !== $omittedLocale;
        });

        $locales[] = $omittedLocale;

        return $locales;
    }
}
