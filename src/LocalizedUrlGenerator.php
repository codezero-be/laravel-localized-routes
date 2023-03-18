<?php

namespace CodeZero\LocalizedRoutes;

use CodeZero\LocalizedRoutes\Facades\LocaleConfig;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Contracts\Routing\UrlRoutable;

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
    public function generateFromRequest($locale = null, $parameters = null, $absolute = true, $keepQuery = true)
    {
        $urlBuilder = UrlBuilder::make(Request::fullUrl());

        $domain = $urlBuilder->getHost();
        $localeSlug = $urlBuilder->getSlugs()[0] ?? null;

        $locale = $locale
            ?? LocaleConfig::findLocaleBySlug($localeSlug)
            ?? LocaleConfig::findLocaleByDomain($domain)
            ?? App::getLocale();

        if ( ! $this->is404()) {
            // $parameters can be an array, a function or it can contain model instances!
            // Normalize the parameters so we end up with an array of key => value pairs.
            $parameters = $this->prepareParameters($locale, $parameters ?: $this->getRouteParameters());

            $urlBuilder->setPath($this->route->uri());

            list($slugs, $query) = $this->extractQueryParameters($urlBuilder->getPath(), $parameters);

            if (count($query)) {
                $urlBuilder->setQuery($query);
            }

            if ($url = $this->generateFromNamedRoute($locale, $parameters, $absolute)) {
                return empty($query) && $keepQuery ? $url . $urlBuilder->getQueryString() : $url;
            }

            $urlBuilder->setPath($this->replaceParameters($this->route->uri(), $slugs));
        }

        // If custom domains are not used and it is not a registered,
        // non localized route, update the locale slug in the path.
        if ( ! LocaleConfig::hasCustomDomains() && ($this->is404() || $this->isLocalized())) {
            $urlBuilder->setSlugs($this->updateLocaleInSlugs($urlBuilder->getSlugs(), $locale));
        }

        if ($domain = LocaleConfig::findDomainByLocale($locale)) {
            $urlBuilder->setHost($domain);
        }

        if ($keepQuery === false) {
            $urlBuilder->setQuery([]);
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
        $routeAction = LocaleConfig::getRouteAction();

        return $this->routeExists() && $this->route->getAction($routeAction) !== null;
    }

    /**
     * Check if the current request is a 404.
     * Default 404 requests will not have a Route.
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
     *
     * @return bool
     */
    protected function routeExists()
    {
        return $this->route !== null;
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

        if (LocaleConfig::hasCustomSlugs()) {
            $locale = LocaleConfig::findLocaleBySlug($locale);
        }

        return ($locale && LocaleConfig::isSupportedLocale($locale)) ? $locale : null;
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
        $slug = LocaleConfig::findSlugByLocale($locale);

        if ($this->getLocaleFromSlugs($slugs)) {
            $slugs[0] = $slug;
        } else {
            array_unshift($slugs, $slug);
        }

        if ($locale === LocaleConfig::getOmittedLocale()) {
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
        $models = Collection::make($parameters)->filter(function ($model) {
            return $model instanceof ProvidesRouteParameters;
        });

        if ($models->count()) {
            $parameters = $models->flatMap(function ($model) use ($locale) {
                return $model->getRouteParameters($locale);
            })->all();
        }

        if (is_callable($parameters)) {
            $parameters = $parameters($locale);
        }

        foreach ($parameters as $key => $parameter) {
            if ($parameter instanceof UrlRoutable) {
                $parameters[$key] = $this->getLocalizedRouteKey($key, $parameter, $locale);
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
     * @param string $key
     * @param \Illuminate\Contracts\Routing\UrlRoutable $model
     * @param string $locale
     *
     * @return string
     */
    protected function getLocalizedRouteKey($key, UrlRoutable $model, $locale)
    {
        $originalLocale = App::getLocale();

        App::setLocale($locale);

        $bindingField = $this->getBindingFieldFor($key, $model);
        $routeKey = $model->$bindingField;

        App::setLocale($originalLocale);

        return $routeKey;
    }

    /**
     * Get the binding field for the current route.
     *
     * The binding field is the custom route key that you can define in your route:
     * Route::get('path/{model:key}')
     * If you did not use a custom key, use the default route key.
     *
     * @param string|int $key
     * @param \Illuminate\Contracts\Routing\UrlRoutable $model
     *
     * @return string|null
     */
    protected function getBindingFieldFor($key, UrlRoutable $model)
    {
        if (version_compare(App::version(), '7.0.0') === -1) {
            return $model->getRouteKeyName();
        }

        return $this->route->bindingFieldFor($key) ?: $model->getRouteKeyName();
    }
}
