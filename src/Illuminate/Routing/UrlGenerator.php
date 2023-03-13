<?php

namespace CodeZero\LocalizedRoutes\Illuminate\Routing;

use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class UrlGenerator extends BaseUrlGenerator
{
    /**
     * Resolve the URL to a named route or a localized version of it.
     *
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @param string|null $locale
     *
     * @return string
     */
    public function route($name, $parameters = [], $absolute = true, $locale = null)
    {
        // Cache the current locale, so we can change it to automatically
        // resolve any translatable route parameters such as slugs.
        $currentLocale = App::getLocale();

        $resolvedName = $this->resolveLocalizedRouteName($name, $locale, $currentLocale);

        // Update the current locale if needed.
        if ($locale !== null && $locale !== $currentLocale) {
            App::setLocale($locale);
        }

        $url = parent::route($resolvedName, $parameters, $absolute);

        // Restore the current locale if needed.
        if ($locale !== null && $locale !== $currentLocale) {
            App::setLocale($currentLocale);
        }

        return $url;
    }

    /**
     * Create a signed route URL for a named route.
     *
     * @param string $name
     * @param mixed $parameters
     * @param \DateInterval|\DateTimeInterface|int|null $expiration
     * @param bool $absolute
     * @param string|null $locale
     *
     * @return string
     */
    public function signedRoute($name, $parameters = [], $expiration = null, $absolute = true, $locale = null)
    {
        // Cache the current locale, so we can change it to automatically
        // resolve any translatable route parameters such as slugs.
        $currentLocale = App::getLocale();

        $resolvedName = $this->resolveLocalizedRouteName($name, $locale, $currentLocale);

        // Update the current locale if needed.
        if ($locale !== null && $locale !== $currentLocale) {
            App::setLocale($locale);
        }

        $url = parent::signedRoute($resolvedName, $parameters, $expiration, $absolute);

        // Restore the current locale if needed.
        if ($locale !== null && $locale !== $currentLocale) {
            App::setLocale($currentLocale);
        }

        return $url;
    }

    /**
     * Resolve a localized version of the route name in the given locale.
     *
     * @param string $name
     * @param string|null $locale
     * @param string $currentLocale
     *
     * @return string
     */
    protected function resolveLocalizedRouteName($name, $locale, $currentLocale)
    {
        // If the route exists, and we're not requesting a specific locale,
        // let the base class resolve the route.
        if (Route::has($name) && $locale === null) {
            return $name;
        }

        // Use the specified or current locale
        // as a prefix for the route name.
        $locale = $locale ?: $currentLocale;

        // If the locale is not supported, use a fallback
        // locale if one is configured.
        if ( ! $this->isSupportedLocale($locale)) {
            $locale = $this->getFallbackLocale() ?: $locale;
        }

        // Normalize the route name by removing any locale prefix.
        // We will prepend the applicable locale manually.
        $baseName = $this->stripLocaleFromRouteName($name);

        // If the route has a name (not just the locale prefix)
        // add the requested locale prefix.
        $newName = $baseName ? "{$locale}.{$baseName}" : '';

        // If the new localized route name does not exist, but the unprefixed route name does,
        // someone is calling "route($name, [], true, $locale)" on a non localized route.
        // In that case, resolve the unprefixed route name.
        if (Route::has($baseName) && ! Route::has($newName)) {
            $newName = $baseName;
        }

        return $newName;
    }

    /**
     * Strip the locale from the beginning of a route name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function stripLocaleFromRouteName($name)
    {
        $parts = explode('.', $name);

        // If there is no dot in the route name,
        // there is no locale in the route name.
        if (count($parts) === 1) {
            return $name;
        }

        // If the first part of the route name is a valid
        // locale, then remove it from the array.
        if ($this->isSupportedLocale($parts[0])) {
            array_shift($parts);
        }

        // Rebuild the normalized route name.
        $name = join('.', $parts);

        return $name;
    }

    /**
     * Check if the given locale is supported.
     *
     * @param string $locale
     *
     * @return bool
     */
    protected function isSupportedLocale($locale)
    {
        return in_array($locale, $this->getSupportedLocales());
    }

    /**
     * Get the supported locales and not the custom slugs or domains.
     *
     * @return array
     */
    protected function getSupportedLocales()
    {
        $locales = Config::get('localized-routes.supported_locales', []);

        if (is_numeric(key($locales))) {
            return $locales;
        }

        return array_keys($locales);
    }

    /**
     * Get the fallback locale.
     *
     * @return string|null
     */
    protected function getFallbackLocale()
    {
        return Config::get('localized-routes.fallback_locale');
    }
}
