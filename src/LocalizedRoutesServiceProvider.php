<?php

namespace CodeZero\LocalizedRoutes;

use CodeZero\LocalizedRoutes\Macros\IsLocalizedMacro;
use CodeZero\LocalizedRoutes\Macros\LocalizedUrlMacro;
use CodeZero\LocalizedRoutes\Macros\UriTranslationMacro;
use CodeZero\LocalizedRoutes\Macros\LocalizedRoutesMacro;
use CodeZero\Localizer\Localizer;
use Illuminate\Support\ServiceProvider;

class LocalizedRoutesServiceProvider extends ServiceProvider
{
    /**
     * The package name.
     *
     * @var string
     */
    protected $name = 'localized-routes';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishableFiles();
        $this->registerMacros();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
        $this->registerUrlGenerator();
        $this->registerLocaleHandler();
    }

    /**
     * Register macros.
     *
     * @return void
     */
    protected function registerMacros()
    {
        IsLocalizedMacro::register();
        LocalizedRoutesMacro::register();
        LocalizedUrlMacro::register();
        UriTranslationMacro::register();
    }

    /**
     * Register the publishable files.
     *
     * @return void
     */
    protected function registerPublishableFiles()
    {
        $this->publishes([
            __DIR__."/../config/{$this->name}.php" => config_path("{$this->name}.php"),
        ], 'config');
    }

    /**
     * Merge published configuration file with
     * the original package configuration file.
     *
     * @return void
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(__DIR__."/../config/{$this->name}.php", $this->name);
    }

    /**
     * Register the URL generator service.
     *
     * The UrlGenerator class that is instantiated is determined
     * by the "use" statement at the top of this file.
     *
     * This method is an exact copy from:
     * \Illuminate\Routing\RoutingServiceProvider
     *
     * @return void
     */
    protected function registerUrlGenerator()
    {
        $this->app->singleton('url', function ($app) {
            $routes = $app['router']->getRoutes();

            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            $app->instance('routes', $routes);

            $url = new UrlGenerator(
                $routes, $app->rebinding(
                'request', $this->requestRebinder()
            ), $app['config']['app.asset_url']
            );

            // Next we will set a few service resolvers on the URL generator so it can
            // get the information it needs to function. This just provides some of
            // the convenience features to this URL generator like "signed" URLs.
            $url->setSessionResolver(function () {
                return $this->app['session'];
            });

            $url->setKeyResolver(function () {
                return $this->app->make('config')->get('app.key');
            });

            // If the route collection is "rebound", for example, when the routes stay
            // cached for the application, we will need to rebind the routes on the
            // URL generator instance so it has the latest version of the routes.
            $app->rebinding('routes', function ($app, $routes) {
                $app['url']->setRoutes($routes);
            });

            return $url;
        });
    }

    /**
     * Get the URL generator request rebinder.
     *
     * This method is an exact copy from:
     * \Illuminate\Routing\RoutingServiceProvider
     *
     * @return \Closure
     */
    protected function requestRebinder()
    {
        return function ($app, $request) {
            $app['url']->setRequest($request);
        };
    }

    /**
     * Register the LocaleHandler..
     *
     * @return void
     */
    protected function registerLocaleHandler()
    {
        $this->app->bind(LocaleHandler::class, function ($app) {
            $supportedLocales = $app['config']->get('localized-routes.supported-locales', []);
            $useLocalizer = $app['config']->get('localized-routes.use_localizer', false);
            $localizer = $useLocalizer ? $app->make(Localizer::class) : null;

            return new LocaleHandler($this->formatLocales($supportedLocales), $localizer);
        });
    }

    /**
     * Format the locales to pass them to Localizer.
     *
     * @param array $locales
     *
     * @return array
     */
    protected function formatLocales($locales)
    {
        $keys = array_keys($locales);

        // If the locales are in a key => value format (locale => domain)
        // then only return the keys; else return the original array.
        if (isset($keys[0]) && is_numeric($keys[0])) {
            return $locales;
        }

        return $keys;
    }
}
