<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit\Macros;

use CodeZero\LocalizedRoutes\Tests\TestCase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

class LocalizedRoutesMacroTest extends TestCase
{
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
        $this->setOmitUrlPrefixForLocale('en');

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
    public function it_registers_routes_in_the_correct_order_without_prefix_for_a_configured_main_locale()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setOmitUrlPrefixForLocale('en');
        $this->setUseLocaleMiddleware(true);

        Route::localized(function () {
            Route::get('/', function () { return 'Home '.App::getLocale(); });
            Route::get('{slug}', function () { return 'Dynamic '.App::getLocale(); });
        });

        $this->assertEquals(
            ['nl', 'nl/{slug}', '/', '{slug}'],
            $this->getRoutes()->pluck('uri')->toArray()
        );

        $response = $this->call('GET', '/');
        $response->assertOk();
        $this->assertEquals('Home en', $response->original);

        $response = $this->call('GET', '/nl');
        $response->assertOk();
        $this->assertEquals('Home nl', $response->original);

        $response = $this->call('GET', '/dynamic');
        $response->assertOk();
        $this->assertEquals('Dynamic en', $response->original);

        $response = $this->call('GET', '/nl/dynamic');
        $response->assertOk();
        $this->assertEquals('Dynamic nl', $response->original);
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
    public function it_does_not_permanently_change_the_locale_without_middleware()
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
}
