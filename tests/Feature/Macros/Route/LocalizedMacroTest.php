<?php

namespace CodeZero\LocalizedRoutes\Tests\Feature\Macros\Route;

use PHPUnit\Framework\Attributes\Test;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class LocalizedMacroTest extends TestCase
{
    #[Test]
    public function it_registers_a_route_for_each_locale(): void
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

    #[Test]
    public function it_registers_a_root_route_for_each_locale(): void
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

    #[Test]
    public function it_registers_a_url_without_prefix_for_a_configured_main_locale(): void
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setOmittedLocale('en');

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

    #[Test]
    public function it_registers_routes_in_the_correct_order_without_prefix_for_a_configured_main_locale(): void
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setOmittedLocale('en');

        Route::localized(function () {
            Route::get('/', function () {});
            Route::get('{slug}', function () {});
        });

        $this->assertEquals(
            ['nl', 'nl/{slug}', '/', '{slug}'],
            $this->getRoutes()->pluck('uri')->toArray()
        );
    }

    #[Test]
    public function it_maps_a_custom_slug_to_each_locale(): void
    {
        $this->setSupportedLocales([
            'en' => 'english',
            'nl' => 'dutch',
        ]);

        Route::localized(function () {
            Route::get('/', function () {})
                ->name('home');
        });

        $routes = $this->getRoutes();

        $route = $routes->first();
        $this->assertEquals('en.home', $route->action['as']);
        $this->assertEquals('english', $route->uri);

        $route = $routes->last();
        $this->assertEquals('nl.home', $route->action['as']);
        $this->assertEquals('dutch', $route->uri);
    }

    #[Test]
    public function it_registers_routes_in_the_correct_order_without_prefix_for_a_configured_main_locale_with_custom_slugs(): void
    {
        $this->setSupportedLocales([
            'en' => 'english',
            'nl' => 'dutch',
        ]);
        $this->setOmittedLocale('en');

        Route::localized(function () {
            Route::get('/', function () {});
            Route::get('{slug}', function () {});
        });

        $this->assertEquals(
            ['dutch', 'dutch/{slug}', '/', '{slug}'],
            $this->getRoutes()->pluck('uri')->toArray()
        );
    }

    #[Test]
    public function it_maps_a_custom_domain_to_each_locale(): void
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

    #[Test]
    public function it_registers_routes_in_the_correct_order_without_prefix_for_a_configured_main_locale_with_domains(): void
    {
        $this->setSupportedLocales([
            'en' => 'english-domain.com',
            'nl' => 'dutch-domain.com',
        ]);
        $this->setOmittedLocale('en');

        Route::localized(function () {
            Route::get('/', function () {})->name('home');
            Route::get('{slug}', function () {})->name('catch-all');
        });

        $routes = $this->getRoutes();

        $this->assertCount(4, $routes);

        $route = $routes[0];
        $this->assertEquals('english-domain.com', $route->action['domain']);
        $this->assertEquals('en.home', $route->action['as']);
        $this->assertEquals('/', $route->uri);

        $route = $routes[1];
        $this->assertEquals('english-domain.com', $route->action['domain']);
        $this->assertEquals('en.catch-all', $route->action['as']);
        $this->assertEquals('{slug}', $route->uri);

        $route = $routes[2];
        $this->assertEquals('dutch-domain.com', $route->action['domain']);
        $this->assertEquals('nl.home', $route->action['as']);
        $this->assertEquals('/', $route->uri);

        $route = $routes[3];
        $this->assertEquals('dutch-domain.com', $route->action['domain']);
        $this->assertEquals('nl.catch-all', $route->action['as']);
        $this->assertEquals('{slug}', $route->uri);
    }

    #[Test]
    public function it_uses_scoped_config_options(): void
    {
        $this->setSupportedLocales(['en']);
        $this->setOmittedLocale(null);

        Config::set('localized-routes.route_action', 'localized-routes-locale');

        Route::localized(function () {
            Route::get('with-scoped-config', function () {})
                ->name('scoped');
        }, [
            'omitted_locale' => 'en',
            'supported_locales' => ['en', 'nl', 'de'],
        ]);

        $routes = $this->getRoutes();

        $this->assertCount(3, $routes);

        $route = $routes[0];
        $this->assertEquals('nl', $route->action['localized-routes-locale']);
        $this->assertEquals('nl.scoped', $route->action['as']);
        $this->assertEquals('nl/with-scoped-config', $route->uri);

        $route = $routes[1];
        $this->assertEquals('de', $route->action['localized-routes-locale']);
        $this->assertEquals('de.scoped', $route->action['as']);
        $this->assertEquals('de/with-scoped-config', $route->uri);

        $route = $routes[2];
        $this->assertEquals('en', $route->action['localized-routes-locale']);
        $this->assertEquals('en.scoped', $route->action['as']);
        $this->assertEquals('with-scoped-config', $route->uri);
    }
}
