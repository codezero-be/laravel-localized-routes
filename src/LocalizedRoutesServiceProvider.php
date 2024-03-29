<?php

namespace CodeZero\LocalizedRoutes;

use CodeZero\BrowserLocale\Laravel\BrowserLocaleServiceProvider;
use CodeZero\LocalizedRoutes\Illuminate\Routing\Redirector;
use CodeZero\LocalizedRoutes\Illuminate\Routing\UrlGenerator;
use CodeZero\LocalizedRoutes\Macros\Route\HasLocalizedMacro;
use CodeZero\LocalizedRoutes\Macros\Route\IsFallbackMacro;
use CodeZero\LocalizedRoutes\Macros\Route\IsLocalizedMacro;
use CodeZero\LocalizedRoutes\Macros\Route\LocalizedMacro;
use CodeZero\LocalizedRoutes\Macros\Route\LocalizedUrlMacro;
use CodeZero\LocalizedRoutes\Middleware\LocaleHandler;
use CodeZero\UriTranslator\UriTranslatorServiceProvider;
use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
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
        $this->registerLocaleConfig();
        $this->registerLocaleHandler();
        $this->registerUrlGenerator();
        $this->registerRedirector();
        $this->registerProviders();
    }

    /**
     * Register macros.
     *
     * @return void
     */
    protected function registerMacros()
    {
        HasLocalizedMacro::register();
        IsFallbackMacro::register();
        IsLocalizedMacro::register();
        LocalizedMacro::register();
        LocalizedUrlMacro::register();
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
     * Registers the package dependencies
     *
     * @return void
     */
    protected function registerProviders()
    {
        $this->app->register(BrowserLocaleServiceProvider::class);
        $this->app->register(UriTranslatorServiceProvider::class);
    }

    /**
     * Register the LocaleConfig binding.
     *
     * @return void
     */
    protected function registerLocaleConfig()
    {
        $this->app->bind(LocaleConfig::class, function ($app) {
            return new LocaleConfig($app['config'][$this->name]);
        });

        $this->app->bind('locale-config', LocaleConfig::class);
    }

    /**
     * Register LocaleHandler.
     *
     * @return void
     */
    protected function registerLocaleHandler()
    {
        $this->app->bind(LocaleHandler::class, function ($app) {
            $locales = $app['locale-config']->getLocales();
            $detectors = $app['config']->get("{$this->name}.detectors");
            $stores = $app['config']->get("{$this->name}.stores");
            $trustedDetectors = $app['config']->get("{$this->name}.trusted_detectors");

            return new LocaleHandler($locales, $detectors, $stores, $trustedDetectors);
        });
    }

    /**
     * Register a custom URL generator that extends the one that comes with Laravel.
     * This will override a few methods that enables us to generate localized URLs.
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

            return new UrlGenerator(
                $routes, $app->rebinding(
                    'request', $this->requestRebinder()
                ), $app['config']['app.asset_url']
            );
        });

        $this->app->extend('url', function (UrlGeneratorContract $url, $app) {
            // Next we will set a few service resolvers on the URL generator so it can
            // get the information it needs to function. This just provides some of
            // the convenience features to this URL generator like "signed" URLs.
            $url->setSessionResolver(function () {
                return $this->app['session'] ?? null;
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
     * Register a custom URL redirector that extends the one that comes with Laravel.
     * This will override a few methods that enables us to redirect to localized URLs.
     *
     * This method is an exact copy from:
     * \Illuminate\Routing\RoutingServiceProvider
     *
     * @return void
     */
    protected function registerRedirector()
    {
        $this->app->singleton('redirect', function ($app) {
            $redirector = new Redirector($app['url']);

            // If the session is set on the application instance, we'll inject it into
            // the redirector instance. This allows the redirect responses to allow
            // for the quite convenient "with" methods that flash to the session.
            if (isset($app['session.store'])) {
                $redirector->setSession($app['session.store']);
            }

            return $redirector;
        });
    }
}
