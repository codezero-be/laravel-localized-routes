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
     * Supported locales config.
     *
     * @var array
     */
    protected $supportedLocales;

    /**
     * Create a new LocalizedUrlGenerator instance.
     */
    public function __construct()
    {
        $this->route = Route::current();
        $this->supportedLocales = $this->getSupportedLocales();
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
        $currentUrl = Request::fullUrl();
        $urlParts = parse_url($currentUrl);
        $domains = $this->getCustomDomains();

        // Replace the host with a matching custom domain
        // or use the current host by default.
        $urlParts['host'] = $domains[$locale] ?? $urlParts['host'];

        if (empty($domains)) {
            // Localize the path if no custom domains are configured.
            $currentPath = $urlParts['path'] ?? '';
            $urlParts['path'] = $this->localizeUrlPath($currentPath, $locale);
        }

        return $this->unparseUrl($urlParts);
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
     * Localize the URL path.
     *
     * @param string $path
     * @param string $requestedLocale
     *
     * @return string
     */
    protected function localizeUrlPath($path, $requestedLocale)
    {
        $slugs = explode('/', trim($path, '/'));

        if (isset($slugs[0]) && $this->localeIsSupported($slugs[0])) {
            // If the existing slug is a supported locale
            // replace it with the requested locale.
            $slugs[0] = $requestedLocale;
        } else {
            // If there is no slug, or it is not a supported locale
            // prepend the requested locale to the slugs array.
            array_unshift($slugs, $requestedLocale);
        }

        if ($this->localeShouldBeOmitted($requestedLocale)) {
            array_shift($slugs);
        }

        return '/' . join('/', $slugs);
    }

    /**
     * Create a string from parsed URL parts.
     *
     * @param array $parts
     *
     * @return string
     */
    protected function unparseUrl(array $parts)
    {
        return $parts['scheme'] . '://' . $parts['host'] . ($parts['port'] ?? '') . ($parts['path'] ?? '');
    }

    /**
     * Check if custom domains are configured.
     *
     * @return bool
     */
    protected function hasCustomDomains()
    {
        $keys = array_keys($this->supportedLocales);

        if (empty($this->supportedLocales) || is_numeric($keys[0])) {
            return false;
        }

        return true;
    }

    /**
     * Get the custom domains from the supported locales configuration.
     *
     * @return array
     */
    protected function getCustomDomains()
    {
        return $this->hasCustomDomains() ? $this->supportedLocales : [];
    }

    /**
     * Get the locale keys from the supported locales configuration.
     *
     * @return array
     */
    protected function getLocaleKeys()
    {
        return $this->hasCustomDomains() ? array_keys($this->supportedLocales) : $this->supportedLocales;
    }

    /**
     * Check if the given locale should be omitted from the URL.
     *
     * @param string $locale
     *
     * @return bool
     */
    protected function localeShouldBeOmitted($locale)
    {
        return $locale === Config::get('localized-routes.omit_url_prefix_for_locale');
    }

    /**
     * Check if the given locale is supported.
     *
     * @param string $locale
     *
     * @return bool
     */
    protected function localeIsSupported($locale)
    {
        return in_array($locale, $this->getLocaleKeys());
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
