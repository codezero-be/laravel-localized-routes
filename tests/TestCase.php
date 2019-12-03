<?php

namespace CodeZero\LocalizedRoutes\Tests;

use CodeZero\LocalizedRoutes\LocalizedRoutesServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends  BaseTestCase
{
    /**
     * Set the supported locales config option.
     *
     * @param $locales
     *
     * @return void
     */
    protected function setSupportedLocales($locales)
    {
        Config::set('localized-routes.supported-locales', $locales);
    }

    /**
     * Set the 'omit_url_prefix_for_locale' config option.
     *
     * @param string $value
     *
     * @return void
     */
    protected function setOmitUrlPrefixForLocale($value)
    {
        Config::set('localized-routes.omit_url_prefix_for_locale', $value);
    }

    /**
     * Set the use_locale_middleware config option
     *
     * @param boolean $value
     *
     * @return void
     */
    protected function setUseLocaleMiddleware($value)
    {
        Config::set('localized-routes.use_locale_middleware', $value);
    }

    /**
     * Get the currently registered routes.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getRoutes()
    {
        // Route::has() doesn't seem to be working
        // when you create routes on the fly.
        // So this is a bit of a workaround...
        return new Collection(Route::getRoutes());
    }

    /**
     * Resolve application Console Kernel implementation.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        // In Laravel 6+, we need to add the middleware to
        // $middlewarePriority in Kernel.php for route
        // model binding to work properly.
        $app->singleton(
            'Illuminate\Contracts\Http\Kernel',
            'CodeZero\LocalizedRoutes\Tests\Stubs\Kernel'
        );
    }

    /**
     * Get the packages service providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            LocalizedRoutesServiceProvider::class,
        ];
    }
}
