<?php

namespace CodeZero\LocalizedRoutes;

use CodeZero\LocalizedRoutes\Facades\LocaleConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

class RouteHelper
{
    /**
     * The current Route.
     *
     * @var \Illuminate\Routing\Route
     */
    protected $route;

    /**
     * Create a new RouteHelper instance.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->route = $request->route();
    }

    /**
     * Check if the current route is a fallback route.
     *
     * @return bool
     */
    public function isFallback(): bool
    {
        return $this->route && $this->route->isFallback;
    }

    /**
     * Check if the current route is localized.
     *
     * @param string|array $patterns
     * @param string|array $locales
     *
     * @return bool
     */
    public function isLocalized($patterns = null, $locales = '*'): bool
    {
        return $patterns === null
            ? $this->isCurrentRouteLocalized()
            : $this->isCurrentRouteLocalizedWithNamePattern($patterns, $locales);
    }

    /**
     * Check if a localized route exists.
     *
     * @param string $name
     * @param string|null $locale
     *
     * @return bool
     */
    public function hasLocalized(string $name, ?string $locale = null): bool
    {
        $locale = $locale ?: App::getLocale();

        return Route::has("{$locale}.{$name}");
    }

    /**
     * Check if the current route is localized.
     *
     * @return bool
     */
    protected function isCurrentRouteLocalized(): bool
    {
        $routeAction = LocaleConfig::getRouteAction();

        return $this->route && $this->route->getAction($routeAction) !== null;
    }

    /**
     * Check if the current route is localized and has a specific name.
     *
     * @param string|array $patterns
     * @param string|array $locales
     *
     * @return bool
     */
    protected function isCurrentRouteLocalizedWithNamePattern($patterns = null, $locales = '*'): bool
    {
        $locales = Collection::make($locales);
        $names = Collection::make();

        Collection::make($patterns)->each(function ($name) use ($locales, $names) {
            $locales->each(function ($locale) use ($name, $names) {
                $names->push($locale . '.' . $name);
            });
        });

        return Route::is($names->all());
    }
}
