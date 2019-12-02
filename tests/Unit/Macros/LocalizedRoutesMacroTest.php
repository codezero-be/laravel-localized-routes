<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit\Macros;

use CodeZero\LocalizedRoutes\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class LocalizedRoutesMacroTest extends TestCase
{
    protected function setSupportedLocales($locales)
    {
        Config::set('localized-routes.supported-locales', $locales);
    }

    /**
     * Set the use_locale_middleware config option
     *
     * @param boolean $value
     * @return void
     */
    protected function setUseLocaleMiddleware($value)
    {
        Config::set('localized-routes.use_locale_middleware', $value);
    }

    /**
     * Set the omit_url_prefix_for_locale config option
     *
     * @param string $value
     * @return void
     */
    protected function setOmitUrlPrefixForLocale($value)
    {
        Config::set('localized-routes.omit_url_prefix_for_locale', $value);
    }

    protected function getRoutes()
    {
        // Route::has() doesn't seem to be working
        // when you create routes on the fly.
        // So this is a bit of a workaround...
        return new Collection(Route::getRoutes());
    }

    /** @test */
    public function it_registers_a_route_for_each_locale()
    {
        $this->setSupportedLocales(['en', 'nl']);

        Route::localized(function () {
            Route::get('route', function () {})
                ->name('route.name');
        });

        $routes = $this->getRoutes();
        $domains = $routes->pluck('action.domain');
        $names = $routes->pluck('action.as');
        $uris = $routes->pluck('uri');

        // Verify that no custom domains are registered.
        $this->assertTrue($domains->filter()->isEmpty());

        $this->assertNotContains('route.name', $names);
        $this->assertContains('en.route.name', $names);
        $this->assertContains('nl.route.name', $names);

        $this->assertNotContains('route', $uris);
        $this->assertContains('en/route', $uris);
        $this->assertContains('nl/route', $uris);
    }

    /** @test */
    public function it_registers_a_root_route_for_each_locale()
    {
        $this->setSupportedLocales(['en', 'nl']);

        Route::localized(function () {
            Route::get('/', function () {})
                ->name('home');
        });

        $routes = $this->getRoutes();
        $names = $routes->pluck('action.as');
        $uris = $routes->pluck('uri');

        $this->assertNotContains('home', $names);
        $this->assertContains('en.home', $names);
        $this->assertContains('nl.home', $names);

        $this->assertNotContains('/', $uris);
        $this->assertContains('en', $uris);
        $this->assertContains('nl', $uris);
    }

    /** @test */
    public function it_registers_a_url_without_prefix_for_a_configured_main_locale()
    {
        $this->setSupportedLocales(['en', 'nl']);

        Config::set('localized-routes.omit_url_prefix_for_locale', 'en');

        Route::localized(function () {
            Route::get('about', function () {})
                ->name('about');
        });

        $routes = $this->getRoutes();
        $names = $routes->pluck('action.as');
        $uris = $routes->pluck('uri');

        $this->assertNotContains('about', $names);
        $this->assertContains('en.about', $names);
        $this->assertContains('nl.about', $names);

        $this->assertNotContains('en/about', $uris);
        $this->assertContains('about', $uris);
        $this->assertContains('nl/about', $uris);
    }

    /** @test */
    public function it_maps_a_custom_domain_to_each_locale()
    {
        $this->setSupportedLocales([
            'en' => 'english-domain.com',
            'nl' => 'dutch-domain.com',
        ]);

        Route::localized(function () {
            Route::get('/', function () {})
                ->name('home');
        });

        $routes = $this->getRoutes();

        $route = $routes->first();
        $this->assertEquals('english-domain.com', $route->action['domain']);
        $this->assertEquals('en.home', $route->action['as']);
        $this->assertEquals('/', $route->uri);

        $route = $routes->last();
        $this->assertEquals('dutch-domain.com', $route->action['domain']);
        $this->assertEquals('nl.home', $route->action['as']);
        $this->assertEquals('/', $route->uri);
    }

    /** @test */
    public function it_temporarily_changes_the_app_locale_when_registering_the_routes()
    {
        $this->setSupportedLocales(['nl']);

        $this->assertEquals('en', App::getLocale());

        Route::localized(function () {
            $this->assertEquals('nl', App::getLocale());
        });

        $this->assertEquals('en', App::getLocale());
    }

    /** @test */
    public function it_does_not_change_the_locale_without_activation()
    {
        $this->setSupportedLocales(['en', 'nl']);

        $originalLocale = App::getLocale();

        Route::localized(function () {
            Route::get('/', function () {
                return App::getLocale();
            });
        });

        $response = $this->call('GET', '/en');
        $response->assertOk();
        $this->assertEquals($originalLocale, $response->original);

        $response = $this->call('GET', '/nl');
        $response->assertOk();
        $this->assertEquals($originalLocale, $response->original);
    }

    /** @test */
    public function it_sets_the_right_locale_when_accessing_localized_routes()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocaleMiddleware(true);

        Route::localized(function () {
            Route::get('/', function () {
                return App::getLocale();
            });
        });

        $response = $this->call('GET', '/en');
        $response->assertOk();
        $this->assertEquals('en', $response->original);

        $response = $this->call('GET', '/nl');
        $response->assertOk();
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_sets_the_right_locale_when_accessing_localized_routes_with_omitted_prefix()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocaleMiddleware(true);
        $this->setOmitUrlPrefixForLocale('en');

        Route::localized(function () {
            Route::get('/', function () {
                return App::getLocale();
            });
        });

        $response = $this->call('GET', '/');
        $response->assertOk();
        $this->assertEquals('en', $response->original);

        $response = $this->call('GET', '/nl');
        $response->assertOk();
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_correctly_uses_scoped_config_options()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setOmitUrlPrefixForLocale(null);
        $this->setUseLocaleMiddleware(false);

        $otherLocale = 'none_of_the_above';

        App::setLocale($otherLocale);

        Route::localized(function () {
            Route::get('/without', function () {
                return App::getLocale();
            });
        });

        Route::localized(function () {
            Route::get('/with', function () {
                return App::getLocale();
            });
        }, [
            'use_locale_middleware' => true,
            'omit_url_prefix_for_locale' => 'en',
            'supported-locales' => ['en', 'nl', 'de']
        ]);

        $response = $this->call('GET', '/without');
        $response->assertNotFound();

        $response = $this->call('GET', '/en/without');
        $response->assertOk();
        $this->assertEquals($otherLocale, $response->original);

        $response = $this->call('GET', '/nl/without');
        $response->assertOk();
        $this->assertEquals($otherLocale, $response->original);

        $response = $this->call('GET', '/with');
        $response->assertOk();
        $this->assertEquals('en', $response->original);

        $response = $this->call('GET', '/nl/with');
        $response->assertOk();
        $this->assertEquals('nl', $response->original);

        $response = $this->call('GET', '/de/with');
        $response->assertOk();
        $this->assertEquals('de', $response->original);
    }

    /** @test */
    public function it_creates_localized_routes_within_route_groups()
    {
        $this->setSupportedLocales(['en', 'nl']);

        Route::group([
            'as' => 'admin.',
            'prefix' => 'admin'
        ], function () {
            Route::localized(function () {
                Route::get('route', function () {})
                    ->name('route.name');
            });
        });

        $routes = $this->getRoutes();
        $domains = $routes->pluck('action.domain');
        $names = $routes->pluck('action.as');
        $uris = $routes->pluck('uri');

        // Verify that no custom domains are registered.
        $this->assertTrue($domains->filter()->isEmpty());

        $this->assertNotContains('admin.route.name', $names);
        $this->assertContains('admin.en.route.name', $names);
        $this->assertContains('admin.nl.route.name', $names);

        $this->assertNotContains('admin/route', $uris);
        $this->assertContains('admin/en/route', $uris);
        $this->assertContains('admin/nl/route', $uris);

        $this->call('GET', '/admin/route')->assertNotFound();
        $this->call('GET', '/admin/en/route')->assertOk();
        $this->call('GET', '/admin/nl/route')->assertOk();
    }

    /** @test */
    public function it_sets_the_locale_for_localized_routes_within_route_groups()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocaleMiddleware(true);

        Route::group([
            'as' => 'admin.',
            'prefix' => 'admin'
        ], function () {
            Route::localized(function () {
                Route::get('route', function () {
                    return App::getLocale();
                })
                    ->name('route.name');
            });
        });

        $response = $this->call('GET', '/admin/en/route');
        $response->assertOk();
        $this->assertEquals('en', $response->original);

        $response = $this->call('GET', '/admin/nl/route');
        $response->assertOk();
        $this->assertEquals('nl', $response->original);
    }
}
