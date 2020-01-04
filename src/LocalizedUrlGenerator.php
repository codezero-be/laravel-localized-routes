<?php

namespace CodeZero\LocalizedRoutes;

use Illuminate\Support\Collection;
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
     * Check if the current route is localized.
     *
     * @return bool
     */
    public function isLocalized()
    {
        return $this->route && $this->route->getAction('localized-routes-locale');
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
        // Route does not exist (default 404)
        if ( ! $this->route) {
            return URL::current();
        }

        return $this->generateFromRoute($locale, $parameters, $absolute);
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
}
