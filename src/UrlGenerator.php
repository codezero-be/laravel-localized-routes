<?php

namespace CodeZero\LocalizedRoutes;

use App;
use Config;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;
use Route;

class UrlGenerator extends BaseUrlGenerator
{
    /**
     * Create a new UrlGenerator instance.
     *
     * @param \Illuminate\Routing\RouteCollection $routes
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(RouteCollection $routes, Request $request)
    {
        parent::__construct($routes, $request);
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

        $locales = Config::get('app.locales', []);

        // If the first part of the route name is a valid
        // locale, then remove it from the array.
        if (in_array($parts[0], $locales)) {
            array_shift($parts);
        }

        // Rebuild the normalized route name.
        $name = join('.', $parts);

        return $name;
    }
}
