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
        $this->setUseLocaleMiddleware(true);

        Route::group(['as' => 'admin.', 'prefix' => 'admin'], function () {
            Route::localized(function () {
                Route::get('route', function () {
                    return App::getLocale();
                });
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
    public function it_allows_for_route_model_binding_using_a_localized_route_key()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocaleMiddleware(true);

        $model = (new Model([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('slug');

        App::instance(Model::class, $model);

        Route::localized(function () {
            Route::get('route/{model}', function (Model $model) {})
                ->middleware(['web']);
        });

        $this->call('GET', '/en/route/en-slug')->assertOk();
        $this->call('GET', '/nl/route/nl-slug')->assertOk();
        $this->call('GET', '/en/route/nl-slug')->assertNotFound();
        $this->call('GET', '/nl/route/en-slug')->assertNotFound();
    }

    /** @test */
    public function it_detects_the_locale_with_localizer_for_non_localized_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocalizer(true);

        $localizer = Mockery::spy(Localizer::class);
        $localizer->shouldReceive('detect')->andReturn('en');
        App::instance(Localizer::class, $localizer);

        Route::get('non-localized-route', function () {})
            ->middleware(['web', SetLocale::class]);

        $this->call('GET', '/non-localized-route')->assertOk();

        $localizer->shouldHaveReceived('setSupportedLocales')->with(['en', 'nl']);
        $localizer->shouldHaveReceived('detect');
        $localizer->shouldHaveReceived('store')->with('en');
    }

    /** @test */
    public function it_does_not_detect_the_locale_with_localizer_for_localized_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocalizer(true);

        $localizer = Mockery::spy(Localizer::class);
        App::instance(Localizer::class, $localizer);

        Route::localized(function () {
            Route::get('localized-route', function () {})
                ->middleware(['web', SetLocale::class]);
        });

        $this->call('GET', '/en/localized-route')->assertOk();

        $localizer->shouldNotHaveReceived('detect');
        $localizer->shouldHaveReceived('store')->with('en');
    }

    /** @test */
    public function it_does_not_use_localizer_when_disabled()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocalizer(false);

        $localizer = Mockery::spy(Localizer::class);
        App::instance(Localizer::class, $localizer);

        Route::localized(function () {
            Route::get('localized-route', function () {})
                ->middleware(['web', SetLocale::class]);
        });

        Route::get('non-localized-route', function () {})
            ->middleware(['web', SetLocale::class]);

        $this->call('GET', '/non-localized-route')->assertOk();
        $this->call('GET', '/en/localized-route')->assertOk();

        $localizer->shouldNotHaveReceived('detect');
        $localizer->shouldNotHaveReceived('store');
    }

    /** @test */
    public function it_still_sets_the_app_locale_for_localized_routes_if_localizer_is_disabled()
    {
        $this->setSupportedLocales(['en']);
        $this->setUseLocalizer(false);
        $this->setAppLocale('fr');

        Route::localized(function () {
            Route::get('localized-route', function () {
                return App::getLocale();
            })->middleware(['web', SetLocale::class]);
        });

        $response = $this->call('GET', '/en/localized-route');
        $response->assertOk();
        $this->assertEquals('en', $response->original);
    }

    /** @test */
    public function it_does_not_set_the_app_locale_for_non_localized_routes_if_localizer_is_disabled()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocalizer(false);
        $this->setAppLocale('fr');

        Route::get('non-localized-route', function () {
            return App::getLocale();
        })->middleware(['web', SetLocale::class]);

        $response = $this->call('GET', '/non-localized-route');
        $response->assertOk();
        $this->assertEquals('fr', $response->original);
    }

    /** @test */
    public function it_passes_the_supported_locales_to_localizer_in_the_correct_format()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en' => 'en.domain.com', 'nl' => 'nl.domain.com']);
        $this->setUseLocalizer(true);

        $localizer = Mockery::spy(Localizer::class);
        $localizer->shouldReceive('detect')->andReturn('en');
        App::instance(Localizer::class, $localizer);

        Route::get('route', function () {})
            ->middleware(['web', SetLocale::class]);

        $this->call('GET', '/route')->assertOk();

        $localizer->shouldHaveReceived('setSupportedLocales')->with(['en', 'nl']);
    }
}
