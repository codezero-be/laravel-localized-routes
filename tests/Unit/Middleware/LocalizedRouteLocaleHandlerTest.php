<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit\Macros;

use CodeZero\LocalizedRoutes\Middleware\LocalizedRouteLocaleHandler;
use CodeZero\LocalizedRoutes\Tests\Stubs\Model;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

class LocalizedRouteLocaleHandlerTest extends TestCase
{
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
        $this->setOmitUrlPrefixForLocale('nl');

        Route::localized(function () {
            Route::get('/', function () {
                return App::getLocale();
            });
        });

        $response = $this->call('GET', '/');
        $response->assertOk();
        $this->assertEquals('nl', $response->original);

        $response = $this->call('GET', '/en');
        $response->assertOk();
        $this->assertEquals('en', $response->original);
    }

    /** @test */
    public function it_sets_the_locale_for_localized_routes_within_route_groups()
    {
        $this->setSupportedLocales(['en', 'nl']);

        Route::group([
            'as' => 'admin.',
            'prefix' => 'admin',
        ], function () {
            Route::localized(function () {
                Route::get('route', function () {
                    return App::getLocale();
                })->name('route.name')->middleware(['web', LocalizedRouteLocaleHandler::class]);
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
