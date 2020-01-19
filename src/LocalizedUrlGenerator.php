<?php

namespace CodeZero\LocalizedRoutes;

use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;

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
        $urlBuilder = UrlBuilder::make(Request::fullUrl());
        $locale = $locale ?: $this->detectLocale($urlBuilder);

        // $parameters can be an array, a function or it can contain model instances!
        // Normalize the parameters so we end up with an array of key => value pairs.
        $parameters = $this->prepareParameters($locale, $parameters ?: $this->getRouteParameters());

        if ( ! $this->is404()) {
            $urlBuilder->setPath($this->route->uri());

            list($slugs, $query) = $this->extractQueryParameters($urlBuilder->getPath(), $parameters);

            if (count($query)) {
                $urlBuilder->setQuery($query);
            }

            if ($url = $this->generateFromNamedRoute($locale, $parameters, $absolute)) {
                return empty($query) ? $url . $urlBuilder->getQueryString() : $url;
            }

            $urlBuilder->setPath($this->replaceParameters($this->route->uri(), $slugs));
        }

        // If custom domains are not used and it is not a registered,
        // non localized route, update the locale slug in the path.
        if ( ! $this->hasCustomDomains() && ($this->is404() || $this->isLocalized())) {
            $urlBuilder->setSlugs($this->updateLocaleInSlugs($urlBuilder->getSlugs(), $locale));
        }

        if ($domain = $this->getCustomDomain($locale)) {
            $urlBuilder->setHost($domain);
        }

        return $urlBuilder->build($absolute);
    }

    /**
     * Generate a URL for a named route.
     *
     * @param string $locale
     * @param array $parameters
     * @param bool $absolute
     *
     * @return string
     */
    protected function generateFromNamedRoute($locale, $parameters, $absolute)
    {
        try {
            return route($this->route->getName(), $parameters, $absolute, $locale);
        } catch (InvalidArgumentException $e) {
            return '';
        }
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
     * Check if the current request is a 404.
     *
     * @return bool
     */
    protected function is404()
    {
        return ! $this->routeExists() || $this->isFallback();
    }

    /**
     * Check if the current route is a fallback route.
     *
     * @return bool
     */
    protected function isFallback()
    {
        return $this->route->isFallback;
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
     * Get the domain for the given locale if one is configured.
     *
     * @param string $locale
     *
     * @return string|null
     */
    protected function getCustomDomain($locale)
    {
        return $this->supportedLocales[$locale] ?? null;
    }

    /**
     * Get the custom domains if configured.
     *
     * @return array
     */
    protected function getCustomDomains()
    {
        return $this->hasCustomDomains() ? $this->supportedLocales : [];
    }

    /**
     * Find the locale that belongs to a custom domain.
     *
     * @param string $domain
     *
     * @return false|string
     */
    protected function findLocaleByDomain($domain)
    {
        return array_search($domain, $this->getCustomDomains());
    }

    /**
     * Get the locale from the slugs if it exists.
     *
     * @param array $slugs
     *
     * @return string|null
     */
    protected function getLocaleFromSlugs(array $slugs)
    {
        $locale = $slugs[0] ?? null;

        return ($locale && $this->localeIsSupported($locale)) ? $locale : null;
    }

    /**
     * Replace the locale in the slugs or prepend it if no locale exists yet.
     *
     * @param array $slugs
     * @param string $locale
     *
     * @return array
     */
    protected function setLocaleInSlugs(array $slugs, $locale)
    {
        $slugs[0] = $locale;

        return $slugs;
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
     * Check if the given locale should be omitted from the URL.
     *
     * @param string $locale
     *
     * @return bool
     */
    protected function localeShouldBeOmitted($locale)
    {
        return $locale === $this->getOmitLocale();
    }

    /**
     * Detect the locale.
     *
     * @param \CodeZero\LocalizedRoutes\UrlBuilder $url
     *
     * @return string
     */
    protected function detectLocale(UrlBuilder $url)
    {
        $locale = $this->findLocaleByDomain($url->getHost())
            ?: $this->getLocaleFromSlugs($url->getSlugs());

        return $locale ?: App::getLocale();
    }

    /**
     * Localize the URL path.
     *
     * @param array $slugs
     * @param string $locale
     *
     * @return array
     */
    protected function updateLocaleInSlugs(array $slugs, $locale)
    {
        if ($this->getLocaleFromSlugs($slugs)) {
            $slugs = $this->setLocaleInSlugs($slugs, $locale);
        } else {
            array_unshift($slugs, $locale);
        }

        if ($this->localeShouldBeOmitted($locale)) {
            array_shift($slugs);
        }

        return $slugs;
    }

    /**
     * Extract URI parameters and query string parameters.
     *
     * @param string $uri
     * @param array $parameters
     *
     * @return array
     */
    protected function extractQueryParameters($uri, $parameters)
    {
        preg_match_all('/{([a-zA-Z_.-]+\??)}/', $uri, $matches);
        $paramKeys = $matches[1] ?? [];

        $slugs = [];
        $query = [];
        $i = 0;

        foreach ($parameters as $key => $value) {
            // Parameters should be in the same order as the placeholders.
            // $key can be a name or an index, so grab the matching key name from the URI.
            $paramKey = $paramKeys[$i] ?? null;

            // If there is a matching $paramKey,
            // we are dealing with a normal parameter,
            // else we are dealing with a query string parameter.
            if ($paramKey) {
                $slugs["{{$paramKey}}"] = $value;
            } else {
                $query[$key] = $value;
            }

            $i++;
        }

        return [$slugs, $query];
    }

    /**
     * Replace parameter placeholders with their value.
     *
     * @param string $uri
     * @param array $parameters
     *
     * @return string
     */
    protected function replaceParameters($uri, $parameters)
    {
        foreach ($parameters as $placeholder => $value) {
            $uri = str_replace($placeholder, $value, $uri);
        }

        $uri = preg_replace('/{[a-zA-Z_.-]+\?}/', '', $uri);

        return $uri;
    }

    /**
     * Prepare any route parameters.
     *
     * @param string $locale
     * @param mixed $parameters
     *
     * @return array
     */
    protected function prepareParameters($locale, $parameters)
    {
        $model = Collection::make($parameters)->first();

        if ($model instanceof ProvidesRouteParameters) {
            $parameters = $model->getRouteParameters($locale);
        }

        if (is_callable($parameters)) {
            $parameters = $parameters($locale);
        }

        foreach ($parameters as $key => $parameter) {
            if ($parameter instanceof UrlRoutable) {
                $parameters[$key] = $this->getLocalizedRouteKey($parameter, $locale);
            }
        }

        return $parameters;
    }

    /**
     * Get the current route's parameters.
     *
     * @return array
     */
    protected function getRouteParameters()
    {
        return $this->routeExists() ? $this->route->parameters() : [];
    }

    /**
     * Get the localized route key from a model.
     *
     * @param \Illuminate\Contracts\Routing\UrlRoutable $model
     * @param string $locale
     *
     * @return string
     */
    protected function getLocalizedRouteKey(UrlRoutable $model, $locale)
    {
        $originalLocale = App::getLocale();

        App::setLocale($locale);

        $routeKey = $model->getRouteKey();

        App::setLocale($originalLocale);

        return $routeKey;
    }

    /**
     * Get the locale that should be omitted in the URI path.
     *
     * @return string|null
     */
    protected function getOmitLocale()
    {
        return Config::get('localized-routes.omit_url_prefix_for_locale', null);
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
