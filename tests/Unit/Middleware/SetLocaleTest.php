<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit\Macros;

use CodeZero\LocalizedRoutes\Middleware\SetLocale;
use CodeZero\LocalizedRoutes\Tests\Stubs\Model;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use CodeZero\Localizer\Localizer;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Mockery;

class SetLocaleTest extends TestCase
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
                })->name('route.name')->middleware(['web', SetLocale::class]);
            });
        });

        $response = $this->call('GET', '/admin/en/route');
        $response->assertOk();
        $this->assertEquals('en', $response->original);

        $response = $this->call('GET', '/admin/nl/route');
        $response->assertOk();
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_allows_for_localized_route_model_binding()
    {
        $this->setSupportedLocales(['en', 'nl']);

        Route::localized(function () {
            Route::get('route/{model}', function (Model $model) {
                return App::getLocale();
            })->name('route.name')->middleware(['web', SetLocale::class]);
        });

        $this->call('GET', '/en/route/en-slug')->assertOk();
        $this->call('GET', '/nl/route/nl-slug')->assertOk();
        $this->call('GET', '/en/route/nl-slug')->assertNotFound();
        $this->call('GET', '/nl/route/en-slug')->assertNotFound();
    }

    /** @test */
    public function it_does_not_detect_the_preferred_locale_with_localizer_for_localized_routes()
    {
        $this->setSupportedLocales(['en', 'nl']);

        $localizer = Mockery::mock(Localizer::class);
        $localizer->shouldReceive('setSupportedLocales')->with(['en', 'nl']);
        $localizer->shouldNotReceive('detect');
        $localizer->shouldReceive('store')->with('en');

        App::instance(Localizer::class, $localizer);

        Route::localized(function () {
            Route::get('localized-route', function () {})
                ->name('localized.route')
                ->middleware(['web', SetLocale::class]);
        });

        $this->call('GET', '/en/localized-route')->assertOk();
    }

    /** @test */
    public function it_detects_the_preferred_locale_with_localizer_for_non_localized_routes()
    {
        $this->setSupportedLocales(['en', 'nl']);

        $localizer = Mockery::mock(Localizer::class);
        $localizer->shouldReceive('setSupportedLocales')->with(['en', 'nl']);
        $localizer->shouldReceive('detect')->andReturn('en');
        $localizer->shouldReceive('store')->with('en');

        App::instance(Localizer::class, $localizer);

        Route::get('non-localized-route', function () {})
            ->name('non-localized.route')
            ->middleware(['web', SetLocale::class]);

        $this->call('GET', '/non-localized-route')->assertOk();
    }
}
