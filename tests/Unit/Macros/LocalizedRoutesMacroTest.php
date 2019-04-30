<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit\Macros;

use App;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use Config;
use Illuminate\Support\Collection;
use Route;

class LocalizedRoutesMacroTest extends TestCase
{
    protected function setSupportedLocales($locales)
    {
        Config::set('localized-routes.supported-locales', $locales);
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
}
