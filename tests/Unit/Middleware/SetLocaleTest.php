<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit\Middleware;

use CodeZero\LocalizedRoutes\Middleware\SetLocale;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use CodeZero\Localizer\Localizer;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Mockery;

class SetLocaleTest extends TestCase
{
    /** @test */
    public function it_uses_localizer()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en']);

        $localizer = Mockery::spy(Localizer::class);
        $localizer->shouldReceive('detect')->andReturn('en');

        App::instance(Localizer::class, $localizer);

        Route::get('some/route', function () {})
            ->middleware(['web', SetLocale::class]);

        $this->get('some/route')->assertOk();

        $localizer->shouldHaveReceived('detect');
        $localizer->shouldHaveReceived('store')->with('en');
    }

    /** @test */
    public function it_configures_localizer()
    {
        // Configure this package
        Config::set('localized-routes.supported-locales', ['en']);
        Config::set('localized-routes.omit_url_prefix_for_locale', 'en');

        // Check Localizer default config
        $this->assertEquals([], Config::get('localizer.supported-locales'));
        $this->assertEquals(null, Config::get('localizer.omitted-locale'));
        $this->assertEquals('locale', Config::get('localizer.route-action'));
        $this->assertEquals([], Config::get('localizer.trusted-detectors'));

        Route::get('some/route', function () {})
            ->middleware(['web', SetLocale::class]);

        $this->get('some/route');

        // Check Localizer updated config
        $this->assertEquals(['en'], Config::get('localizer.supported-locales'));
        $this->assertEquals('en', Config::get('localizer.omitted-locale'));
        $this->assertEquals('localized-routes-locale', Config::get('localizer.route-action'));
        $this->assertEquals([
            \CodeZero\Localizer\Detectors\RouteActionDetector::class
        ], Config::get('localizer.trusted-detectors'));
    }

    /** @test */
    public function it_sets_the_locale()
    {
        $this->setSupportedLocales(['en', 'nl']);

        Route::localized(function () {
            Route::get('/', function () {
                return App::getLocale();
            })->middleware(['web', SetLocale::class]);
        });

        $response = $this->get('en');
        $this->assertEquals('en', $response->original);

        $response = $this->get('nl');
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_sets_the_locale_with_omitted_prefix()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setOmitUrlPrefixForLocale('nl');

        Route::localized(function () {
            Route::get('/', function () {
                return App::getLocale();
            })->middleware(['web', SetLocale::class]);
        });

        $response = $this->get('/');
        $this->assertEquals('nl', $response->original);

        $response = $this->get('en');
        $this->assertEquals('en', $response->original);
    }

    /** @test */
    public function it_sets_the_locale_when_using_custom_slugs()
    {
        $this->setSupportedLocales([
            'en' => 'english',
            'nl' => 'dutch',
        ]);
        $this->setAppLocale('en');

        Route::localized(function () {
            Route::get('some/route', function () {
                return App::getLocale();
            })->middleware(['web', SetLocale::class]);
        });

        $response = $this->get('english/some/route');
        $this->assertEquals('en', $response->original);

        $response = $this->get('dutch/some/route');
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_sets_the_locale_when_using_domains()
    {
        $this->setSupportedLocales([
            'en' => 'english.test',
            'nl' => 'dutch.test',
        ]);
        $this->setAppLocale('en');

        Route::localized(function () {
            Route::get('some/route', function () {
                return App::getLocale();
            })->middleware(['web', SetLocale::class]);
        });

        $response = $this->get('http://english.test/some/route');
        $this->assertEquals('en', $response->original);

        $response = $this->get('http://dutch.test/some/route');
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_sets_the_locale_for_non_localized_fallback_routes_with_omitted_prefix()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setOmitUrlPrefixForLocale('nl');
        $this->setAppLocale('en');

        Route::fallback(function () {
            return App::getLocale();
        })->middleware('web', SetLocale::class);

        $response = $this->get('/non/existing/route');
        $this->assertEquals('nl', $response->original);

        $response = $this->get('/en/non/existing/route');
        $this->assertEquals('en', $response->original);
    }

    /** @test */
    public function it_sets_the_locale_of_routes_with_scoped_config()
    {
        $this->setSupportedLocales(['en']);
        $this->setAppLocale('en');
        $this->setOmitUrlPrefixForLocale(null);

        Route::localized(function () {
            Route::get('with-scoped-config', function () {
                return App::getLocale();
            })->middleware(['web', SetLocale::class]);
        }, [
            'omit_url_prefix_for_locale' => 'en',
            'supported-locales' => ['en', 'nl', 'de'],
        ]);

        $response = $this->get('with-scoped-config');
        $this->assertEquals('en', $response->original);

        $response = $this->get('nl/with-scoped-config');
        $this->assertEquals('nl', $response->original);

        $response = $this->get('de/with-scoped-config');
        $this->assertEquals('de', $response->original);
    }
}
