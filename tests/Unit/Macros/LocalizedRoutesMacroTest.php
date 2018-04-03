<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit\Macros;

use CodeZero\LocalizedRoutes\Tests\TestCase;
use Route;

class LocalizedRoutesMacroTest extends TestCase
{
    protected function setAvailableLocales($locales)
    {
        config()->set('localized-routes.supported-locales', $locales);
    }

    /** @test */
    public function it_registers_a_route_for_each_locale()
    {
        $this->setAvailableLocales(['en', 'nl']);

        Route::localized(function () {
            Route::get('route', function () {})
                ->name('route.name');
        });

        // Route::has() doesn't seem to be working
        // when you create routes on the fly.
        // So this is a bit of a workaround...
        $routes = collect(Route::getRoutes());
        $names = $routes->pluck('action.as');
        $uris = $routes->pluck('uri');

        $this->assertNotContains('route.name', $names);
        $this->assertContains('en.route.name', $names);
        $this->assertContains('nl.route.name', $names);

        $this->assertNotContains('route', $uris);
        $this->assertContains('en/route', $uris);
        $this->assertContains('nl/route', $uris);
    }

    /** @test */
    public function it_temporarily_changes_the_app_locale_when_registering_the_routes()
    {
        $this->setAvailableLocales(['nl']);

        $this->assertEquals('en', app()->getLocale());

        Route::localized(function () {
            $this->assertEquals('nl', app()->getLocale());
        });

        $this->assertEquals('en', app()->getLocale());
    }
}
