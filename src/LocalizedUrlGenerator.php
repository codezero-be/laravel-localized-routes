<?php

namespace CodeZero\LocalizedRoutes;

use CodeZero\LocalizedRoutes\Facades\LocaleConfig;
use CodeZero\UrlBuilder\UrlBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Routing\UrlRoutable;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class LocalizedUrlGenerator
{
    /**
     * The current Request.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The current Route.
     *
     * @var \Illuminate\Routing\Route
     */
    protected $route;

    /**
     * Create a new LocalizedUrlGenerator instance.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->route = $request->route();
    }

    /**
     * Generate a localized URL for the current request.
     *
     * @param string|null $locale
     * @param mixed $parameters
     * @param bool $absolute
     * @param bool $keepQuery
     *
     * @return string
     */
    public function generateFromRequest(string $locale = null, $parameters = null, bool $absolute = true, bool $keepQuery = true): string
    {
        $urlBuilder = UrlBuilder::make($this->request->fullUrl());
        $requestQueryString = $urlBuilder->getQuery();

        $currentDomain = $urlBuilder->getHost();
        $currentLocaleSlug = $urlBuilder->getSlugs()[0] ?? null;

        // Determine in which locale the URL needs to be localized.
        $locale = $locale
            ?? LocaleConfig::findLocaleBySlug($currentLocaleSlug)
            ?? LocaleConfig::findLocaleByDomain($currentDomain)
            ?? App::getLocale();

        if ( ! $this->is404()) {
            // Use the provided parameter values or get them from the current route.
            // Parameters passed to this method may also contain query string parameters.
            // $parameters can be an array, a function, or it can contain model instances!
            // Parameters fetched from the current route will never contain query string parameters.
            // Normalize the parameters, so we end up with an array of key => value pairs.
            $normalizedParameters = $this->normalizeParameters($locale, $parameters ?: $this->getRouteParameters());

            // Get the current route's URI, which has the parameter placeholders.
            $routeUri = $this->route->uri();

            // Separate the route parameters from any query string parameters.
            // $routePlaceholders contains "{key}" => "value" pairs.
            // $routeParameters contains "key" => "value" pairs.
            // $queryStringParameters contains "key" => "value" pairs.
            list($routePlaceholders, $routeParameters, $queryStringParameters) = $this->extractRouteAndQueryStringParameters($routeUri, $normalizedParameters);

            $urlBuilder->setQuery(
                $this->determineQueryStringParameters($requestQueryString, $queryStringParameters, $keepQuery)
            );

            // Generate the URL using the route's name, if possible.
            if ($url = $this->generateNamedRouteURL($locale, $routeParameters, $absolute)) {
                return $urlBuilder->getQueryString() ? $url . '?' . $urlBuilder->getQueryString() : $url;
            }

            // If a named route could not be resolved, replace the parameter
            // placeholders in the URI with their values manually.
            $uriWithParameterValues = $this->replaceParameterPlaceholders($routeUri, $routePlaceholders);
            $urlBuilder->setPath($uriWithParameterValues);
        }

        // If custom domains are not used,
        // and it is either a 404, fallback or localized route,
        // (so it is not a registered, non localized route)
        // update the locale slug in the URI.
        if ( ! LocaleConfig::hasCustomDomains() && ($this->is404() || $this->isLocalized())) {
            $urlBuilder->setSlugs($this->updateLocaleInSlugs($urlBuilder->getSlugs(), $locale));
        }

        // If custom domains are used,
        // find the one for the requested locale.
        if ($domain = LocaleConfig::findDomainByLocale($locale)) {
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
    protected function generateNamedRouteURL(string $locale, array $parameters = [], bool $absolute = true): string
    {
        try {
            return URL::route($this->route->getName(), $parameters, $absolute, $locale);
        } catch (RouteNotFoundException $e) {
            return '';
        }
    }

    /**
     * Check if the current route is localized.
     *
     * @return bool
     */
    protected function isLocalized(): bool
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
    protected function is404(): bool
    {
        return ! $this->routeExists() || $this->isFallback();
    }

    /**
     * Check if the current route is a fallback route.
     *
     * @return bool
     */
    protected function isFallback(): bool
    {
        return $this->routeExists() && $this->route->isFallback;
    }

    /**
     * Check if the current Route exists.
     *
     * @return bool
     */
    protected function routeExists(): bool
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
    protected function getLocaleFromSlugs(array $slugs): ?string
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
    protected function updateLocaleInSlugs(array $slugs, string $locale): array
    {
        $slug = LocaleConfig::findSlugByLocale($locale);

        if ($this->getLocaleFromSlugs($slugs)) {
            array_shift($slugs);
        }

        if ($locale !== LocaleConfig::getOmittedLocale()) {
            array_unshift($slugs, $slug);
        }

        return $slugs;
    }

    /**
     * Determine what query string parameters to use.
     *
     * @param array $requestQueryString
     * @param array $queryStringParameters
     * @param bool $keepQuery
     *
     * @return array
     */
    protected function determineQueryStringParameters(array $requestQueryString, array $queryStringParameters, bool $keepQuery): array
    {
        if ($keepQuery === false) {
            return [];
        }

        if (count($queryStringParameters) > 0) {
            return $queryStringParameters;
        }

        return $requestQueryString;
    }

    /**
     * Extract URI parameters and query string parameters.
     *
     * @param string $uri
     * @param array $parameters
     *
     * @return array
     */
    protected function extractRouteAndQueryStringParameters(string $uri, array $parameters): array
    {
        preg_match_all('/{([a-zA-Z_.-]+\??)}/', $uri, $matches);
        $placeholders = $matches[1] ?? [];

        $routePlaceholders = [];
        $routeParameters = [];
        $queryStringParameters = [];
        $i = 0;

        foreach ($parameters as $key => $value) {
            // Parameters should be in the same order as the placeholders.
            // $key can be a name or an index, so grab the matching key name from the URI.
            $placeholder = $placeholders[$i] ?? null;

            // If there is a matching $paramKey,
            // we are dealing with a normal parameter,
            // else we are dealing with a query string parameter.
            if ($placeholder) {
                $parameterKey = trim($placeholder, '?');
                $routeParameters[$parameterKey] = $value;
                $routePlaceholders["{{$placeholder}}"] = $value;
            } else {
                $queryStringParameters[$key] = $value;
            }

            $i++;
        }

        return [$routePlaceholders, $routeParameters, $queryStringParameters];
    }

    /**
     * Replace parameter placeholders with their value.
     *
     * @param string $uri
     * @param array $parameters
     *
     * @return string
     */
    protected function replaceParameterPlaceholders(string $uri, array $parameters): string
    {
        foreach ($parameters as $placeholder => $value) {
            $uri = str_replace($placeholder, $value, $uri);
        }

        // Remove any optional placeholders that were not provided.
        $uri = preg_replace('/{[a-zA-Z_.-]+\?}/', '', $uri);

        return $uri;
    }

    /**
     * Normalize any route parameters.
     *
     * @param string $locale
     * @param mixed $parameters
     *
     * @return array
     */
    protected function normalizeParameters(string $locale, $parameters): array
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
    protected function getRouteParameters(): array
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
    protected function getLocalizedRouteKey(string $key, UrlRoutable $model, string $locale): string
    {
        $originalLocale = App::getLocale();

        App::setLocale($locale);

        $bindingField = $this->getBindingFieldFor($key);
        $routeKey = $bindingField ? $model->$bindingField : $model->getRouteKey();

        App::setLocale($originalLocale);

        return $routeKey;
    }

    /**
     * Get the binding field for the current route.
     *
     * The binding field is the custom route key that you can define in your route:
     * Route::get('path/{model:key}')
     * If you did not use a custom key, we'll use the default route key.
     *
     * @param string|int $key
     *
     * @return string|null
     */
    protected function getBindingFieldFor($key): ?string
    {
        return $this->route->bindingFieldFor($key);
    }
}
