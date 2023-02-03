<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit;

use CodeZero\LocalizedRoutes\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class HelpersFileTest extends TestCase
{
    /** @test */
    function it_returns_localized_routes_with_locale_argument(): void
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::localized(function () {
            Route::get('route')->name('route');
        });

        $this->assertEquals(url('en/route'), route('route', [], true, null));
        $this->assertEquals(url('en/route'), route('route', [], true, 'en'));
        $this->assertEquals(url('nl/route'), route('route', [], true, 'nl'));
    }

    /** @test */
    function it_throws_when_route_helper_locale_is_unsupported(): void
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::localized(function () {
            Route::get('route')->name('route');
        });

        if (version_compare($this->app->version(), '6.0.0') === -1) {
            $this->expectExceptionMessage('Route [wk.route] not defined.');
        } else {
            $this->expectException(RouteNotFoundException::class);
        }

        route('route', [], true, 'wk');
    }

    /** @test */
    function it_uses_fallback_locale_when_route_helper_locale_is_unsupported(): void
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');
        $this->setFallbackLocale('en');

        Route::localized(function () {
            Route::get('route')->name('route');
        });

        $this->assertEquals(url('en/route'), route('route', [], true, 'en'));
        $this->assertEquals(url('nl/route'), route('route', [], true, 'nl'));
        $this->assertEquals(url('en/route'), route('route', [], true, 'wk'));
    }
}
