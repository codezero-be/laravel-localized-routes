<?php

namespace CodeZero\LocalizedRoutes;

use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class UrlGenerator extends BaseUrlGenerator
{
    /**
     * Create a new URL Generator instance.
     *
     * @param \Illuminate\Routing\RouteCollection $routes
     * @param \Illuminate\Http\Request $request
     * @param string $assetRoot
     */
    public function __construct(RouteCollection $routes, Request $request, $assetRoot = null)
    {
        parent::__construct($routes, $request, $assetRoot);
    }

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
        // If the route exists and we're not requesting a translation,
        // let the base class resolve the route.
        if (Route::has($name) && $locale === null) {
            return parent::route($name, $parameters, $absolute);
        }

        // Normalize the route name by removing any locale prefix.
        // We will prepend the applicable locale manually.
        $name = $this->stripLocaleFromRouteName($name);

        // Cache the current locale so we can change it
        // to automatically resolve any translatable
        // route parameters such as slugs.
        $currentLocale = App::getLocale();

        // Use the specified or current locale
        // as a prefix for the route name.
        $locale = $locale ?: $currentLocale;

        // Update the current locale if needed.
        if ($locale !== $currentLocale) {
            App::setLocale($locale);
        }

        $url = parent::route("{$locale}.{$name}", $parameters, $absolute);

        // Restore the current locale if needed.
        if ($locale !== $currentLocale) {
            App::setLocale($currentLocale);
        }

        return $url;
    }

    /**
     * Create a signed route URL for a named route.
     *
     * @param string $name
     * @param array $parameters
     * @param \DateTimeInterface|\DateInterval|int $expiration
     * @param bool $absolute
     * @param string|null $locale
     *
     * @return string
     */
    public function signedRoute($name, $parameters = [], $expiration = null, $absolute = true, $locale = null)
    {
        $parameters = $this->formatParameters($parameters);

        if ($expiration) {
            $parameters = $parameters + ['expires' => $this->availableAt($expiration)];
        }

        ksort($parameters);

        $key = call_user_func($this->keyResolver);

        return $this->route($name, $parameters + [
                'signature' => hash_hmac('sha256', $this->route($name, $parameters, $absolute, $locale), $key),
            ], $absolute, $locale);
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

        $locales = $this->getSupportedLocales();

        // If the first part of the route name is a valid
        // locale, then remove it from the array.
        if (in_array($parts[0], $locales)) {
            array_shift($parts);
        }

        // Rebuild the normalized route name.
        $name = join('.', $parts);

        return $name;
    }

    /**
     * Get the supported locales and not the custom domains.
     *
     * @return array
     */
    protected function getSupportedLocales()
    {
        $locales = Config::get('localized-routes.supported-locales', []);
        $keys = array_keys($locales);

        if ( ! empty($locales) && is_numeric($keys[0])) {
            return $locales;
        }

        return $keys;
    }
}
