<?php

namespace CodeZero\LocalizedRoutes\Tests;

use CodeZero\LocalizedRoutes\LocalizedRoutesServiceProvider;
use CodeZero\Localizer\LocalizerServiceProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends  BaseTestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if ($this->app->version() < 7) {
            \Illuminate\Foundation\Testing\TestResponse::macro('assertResponseHasNoView', function () {
                if (isset($this->original) && $this->original instanceof View) {
                    Illuminate\Foundation\Testing\Assert::fail('The response has a view.');
                }

                return $this;
            });
        } else {
            \Illuminate\Testing\TestResponse::macro('assertResponseHasNoView', function () {
                if (isset($this->original) && $this->original instanceof View) {
                    Illuminate\Testing\Assert::fail('The response has a view.');
                }

                return $this;
            });
        }

    }

    /**
     * Set the app locale.
     *
     * @param string $locale
     *
     * @return void
     */
    protected function setAppLocale($locale)
    {
        App::setLocale($locale);
    }

    /**
     * Set the supported locales config option.
     *
     * @param array $locales
     *
     * @return void
     */
    protected function setSupportedLocales($locales)
    {
        Config::set('localized-routes.supported-locales', $locales);
    }

    /**
     * Set the fallback locale config option.
     *
     * @param string $locale
     *
     * @return void
     */
    protected function setFallbackLocale($locale): void
    {
        Config::set('localized-routes.fallback_locale', $locale);
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
     * Set the 'redirect_to_localized_urls' config option.
     *
     * @param bool $value
     *
     * @return void
     */
    protected function setRedirectToLocalizedUrls($value)
    {
        Config::set('localized-routes.redirect_to_localized_urls', $value);
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
     * Set the use_localizer config option
     *
     * @param boolean $value
     *
     * @return void
     */
    protected function setUseLocalizer($value)
    {
        Config::set('localized-routes.use_localizer', $value);
    }

    /**
     * Fake that we created a routes.php file in 'resources/lang/'
     * for each language with the given translations.
     *
     * @param $translations
     * @param string $namespace
     *
     * @return void
     */
    protected function setTranslations($translations, $namespace = '*')
    {
        Lang::setLoaded([
            $namespace => [
                'routes' => $translations
            ]
        ]);
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
            LocalizerServiceProvider::class,
            LocalizedRoutesServiceProvider::class,
        ];
    }
}
