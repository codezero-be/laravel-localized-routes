<?php

namespace CodeZero\LocalizedRoutes;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class LocalizedUrlGenerator
{
    /**
     * The current Route.
     *
     * @var \Illuminate\Routing\Route
     */
    protected $route;

    /**
     * Create a new LocalizedUrlGenerator instance.
     */
    public function __construct()
    {
        $this->route = Route::current();
    }

    /**
     * Generate a localized URL for the current request.
     *
     * @param string|null $locale
     * @param mixed $parameters
     * @param bool $absolute
     *
     * @return string
     */
    public function generateFromRequest($locale = null, $parameters = null, $absolute = true)
    {
        return ($this->isDefault404() || $this->isNonLocalizedFallback404())
            ? $this->generateFromUrl($locale, $parameters, $absolute)
            : $this->generateFromRoute($locale, $parameters, $absolute);
    }

    /**
     * Check if the current route is localized.
     *
     * @return bool
     */
    public function isLocalized()
    {
        return $this->routeExists() && $this->route->getAction('localized-routes-locale') !== null;
    }

    /**
     * Check if the current request is a default 404.
     * Default 404 requests will not have a Route.
     *
     * @return bool
     */
    protected function isDefault404()
    {
        return ! $this->routeExists();
    }

    /**
     * Check if the current request is a non localized fallback 404 route.
     * If a fallback route is used as a 404, we expect it to be named '404'.
     *
     * @return bool
     */
    protected function isNonLocalizedFallback404()
    {
        return $this->routeExists() && $this->route->isFallback && $this->route->getName() === '404';
    }

    /**
     * Check if the current Route exists.
     * Default 404 requests will not have a Route.
     *
     * @return bool
     */
    protected function routeExists()
    {
        return $this->route !== null;
    }

    /**
     * Generate a localized version of a URL.
     *
     * @param string|null $locale
     * @param mixed $parameters
     * @param bool $absolute
     *
     * @return string
     */
    protected function generateFromUrl($locale = null, $parameters = null, $absolute = true)
    {
        $locale = $locale ?: App::getLocale();
        $supportedLocales = $this->getSupportedLocales();
        $locales = $this->getLocaleKeys($supportedLocales);
        $domains = $this->getCustomDomains($supportedLocales);
        $currentUrl = Request::fullUrl();
        $urlParts = parse_url($currentUrl);

        if ($domains !== null) {
            $urlParts['host'] = $domains[$locale] ?? $urlParts['host'];
        }

        if ($domains === null) {
            $currentPath = $urlParts['path'] ?? '';
            $slugs = explode('/', trim($currentPath, '/'));
            $localeSlug = $slugs[0] ?? '';

            if (in_array($localeSlug, $locales)) {
                $slugs[0] = $locale;
            } else {
                array_unshift($slugs, $locale);
            }

            if ($slugs[0] === Config::get('localized-routes.omit_url_prefix_for_locale')) {
                $urlParts[0] = '';
            } else {
                $urlParts['path'] = '/' . join('/', $slugs);
            }
        }

        return $urlParts['scheme'] . '://' . $urlParts['host'] . ($urlParts['port'] ?? '') . ($urlParts['path'] ?? '');
    }

    /**
     * Generate a localized URL using the current Route instance.
     *
     * @param string|null $locale
     * @param mixed $parameters
     * @param bool $absolute
     *
     * @return string
     */
    protected function generateFromRoute($locale = null, $parameters = null, $absolute = true)
    {
        if ( ! $this->isLocalized()) {
            return URL::current();
        }

        $parameters = $parameters ?: $this->route->parameters();
        $model = Collection::make($parameters)->first();

        if ($model instanceof ProvidesRouteParameters) {
            $parameters = $model->getRouteParameters($locale);
        }

        if (is_callable($parameters)) {
            $parameters = $parameters($locale);
        }

        return route($this->route->getName(), $parameters, $absolute, $locale);
    }

    /**
     * Get the custom domains from the supported locales configuration.
     *
     * @return array|null
     */
    protected function getCustomDomains(array $locales)
    {
        return $this->hasCustomDomains($locales) ? $locales : null;
    }

    /**
     * Get the locale keys from the supported locales configuration.
     *
     * @param array $locales
     *
     * @return array
     */
    protected function getLocaleKeys(array $locales)
    {
        return $this->hasCustomDomains($locales) ? array_keys($locales) : $locales;
    }

    /**
     * Check if custom domains are configured.
     *
     * @param array $locales
     *
     * @return bool
     */
    protected function hasCustomDomains(array $locales)
    {
        $keys = array_keys($locales);

        if (empty($locales) || is_numeric($keys[0])) {
            return false;
        }

        return true;
    }

    /**
     * Get the supported locales and not the custom domains.
     *
     * @return array
     */
    protected function getSupportedLocales()
    {
        return Config::get('localized-routes.supported-locales', []);

    }
}
