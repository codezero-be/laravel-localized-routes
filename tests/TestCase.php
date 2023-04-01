<?php

namespace CodeZero\LocalizedRoutes\Tests;

use CodeZero\BrowserLocale\BrowserLocale;
use CodeZero\LocalizedRoutes\LocalizedRoutesServiceProvider;
use CodeZero\UriTranslator\UriTranslatorServiceProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Orchestra\Testbench\TestCase as BaseTestCase;
use PHPUnit\Framework\Assert;

abstract class TestCase extends  BaseTestCase
{
    protected $sessionKey;
    protected $cookieName;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('app.key', Str::random(32));

        // Remove any default browser locales
        $this->setBrowserLocales(null);

        $this->sessionKey = Config::get('localized-routes.session_key');
        $this->cookieName = Config::get('localized-routes.cookie_name');

        TestResponse::macro('assertResponseHasNoView', function () {
            if (isset($this->original) && $this->original instanceof View) {
                Assert::fail('The response has a view.');
            }

            return $this;
        });
    }

    /**
     * Skip test if laravel version is lower than the given version.
     *
     * @param string $version
     *
     * @return void
     */
    protected function skipTestIfLaravelVersionIsLowerThan(string $version): void
    {
        if (version_compare(App::version(), $version) === -1) {
            $this->markTestSkipped("This test only applies to Laravel {$version} and higher.");
        }
    }

    /**
     * Set the app locale.
     *
     * @param string $locale
     *
     * @return void
     */
    protected function setAppLocale(string $locale): void
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
    protected function setSupportedLocales(array $locales): void
    {
        Config::set('localized-routes.supported_locales', $locales);
    }

    /**
     * Set the fallback locale config option.
     *
     * @param string|null $locale
     *
     * @return void
     */
    protected function setFallbackLocale(?string $locale): void
    {
        Config::set('localized-routes.fallback_locale', $locale);
    }

    /**
     * Set the 'omitted_locale' config option.
     *
     * @param string|null $locale
     *
     * @return void
     */
    protected function setOmittedLocale(?string $locale): void
    {
        Config::set('localized-routes.omitted_locale', $locale);
    }

    /**
     * Set the locale in the session.
     *
     * @param string $locale
     *
     * @return void
     */
    protected function setSessionLocale(string $locale): void
    {
        Session::put($this->sessionKey, $locale);
    }

    /**
     * Set the locales used by the browser detector.
     *
     * @param string|null $locales
     *
     * @return void
     */
    protected function setBrowserLocales(?string $locales): void
    {
        App::bind(BrowserLocale::class, function () use ($locales) {
            return new BrowserLocale($locales);
        });
    }

    /**
     * Set the 'redirect_to_localized_urls' config option.
     *
     * @param bool $value
     *
     * @return void
     */
    protected function setRedirectToLocalizedUrls(bool $value): void
    {
        Config::set('localized-routes.redirect_to_localized_urls', $value);
    }

    /**
     * Fake that we created a routes.php file in 'resources/lang/'
     * for each language with the given translations.
     *
     * @param array $translations
     * @param string $namespace
     *
     * @return void
     */
    protected function setTranslations(array $translations, string $namespace = '*'): void
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
    protected function getRoutes(): Collection
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
    protected function resolveApplicationHttpKernel($app): void
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
    protected function getPackageProviders($app): array
    {
        return [
            LocalizedRoutesServiceProvider::class,
            UriTranslatorServiceProvider::class,
        ];
    }
}
