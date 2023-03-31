<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit;

use CodeZero\LocalizedRoutes\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
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

        $this->assertEquals(URL::to('en/route'), URL::route('route', [], true, null));
        $this->assertEquals(URL::to('en/route'), URL::route('route', [], true, 'en'));
        $this->assertEquals(URL::to('nl/route'), URL::route('route', [], true, 'nl'));
    }

    /** @test */
    function it_throws_when_route_helper_locale_is_unsupported(): void
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::localized(function () {
            Route::get('route')->name('route');
        });

        $this->expectException(RouteNotFoundException::class);

        URL::route('route', [], true, 'wk');
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

        $this->assertEquals(URL::to('en/route'), URL::route('route', [], true, 'en'));
        $this->assertEquals(URL::to('nl/route'), URL::route('route', [], true, 'nl'));
        $this->assertEquals(URL::to('en/route'), URL::route('route', [], true, 'wk'));
    }
}
