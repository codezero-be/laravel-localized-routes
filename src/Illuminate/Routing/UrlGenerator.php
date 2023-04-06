<?php

namespace CodeZero\LocalizedRoutes\Illuminate\Routing;

use CodeZero\LocalizedRoutes\Facades\LocaleConfig;
use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;
use Illuminate\Support\Facades\App;
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

        try {
            $url = parent::route($resolvedName, $parameters, $absolute);
        } finally {
            // Restore the current locale if needed.
            if ($locale !== null && $locale !== $currentLocale) {
                App::setLocale($currentLocale);
            }
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

        try {
            $url = parent::signedRoute($resolvedName, $parameters, $expiration, $absolute);
        } finally {
            // Restore the current locale if needed.
            if ($locale !== null && $locale !== $currentLocale) {
                App::setLocale($currentLocale);
            }
        }

        return $url;
    }

    /**
     * Create a temporary signed route URL for a named route.
     *
     * @param string $name
     * @param \DateTimeInterface|\DateInterval|int $expiration
     * @param array $parameters
     * @param bool $absolute
     * @param string|null $locale
     *
     * @return string
     */
    public function temporarySignedRoute($name, $expiration, $parameters = [], $absolute = true, $locale = null)
    {
        return $this->signedRoute($name, $parameters, $expiration, $absolute, $locale);
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

        // Normalize the route name by removing any locale prefix.
        // We will prepend the applicable locale manually.
        $baseName = $this->stripLocaleFromRouteName($name);

        if ($baseName === '') {
            return '';
        }

        // Use the specified or current locale
        // as a prefix for the route name.
        $locale = $locale ?: $currentLocale;
        $newName = "{$locale}.{$baseName}";
        $fallbackLocale = LocaleConfig::getFallbackLocale();

        // If the localized route name doesn't exist,
        // use a fallback locale if one is configured.
        if ( ! Route::has($newName) && $fallbackLocale) {
            $newName = "{$fallbackLocale}.{$baseName}";
        }

        // If the unprefixed route name exists, but the new localized route name doesn't,
        // someone may be trying to resolve a localized name in an unsupported locale,
        // e.g. "route('en.route', [], true, 'fr')" (where 'fr.route' doesn't exist and 'route' does)
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
        if (LocaleConfig::isSupportedLocale($parts[0])) {
            array_shift($parts);
        }

        // Rebuild the normalized route name.
        $name = join('.', $parts);

        return $name;
    }
}
