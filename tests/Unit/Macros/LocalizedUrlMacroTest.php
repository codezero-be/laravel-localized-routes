<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit\Macros;

use CodeZero\LocalizedRoutes\Middleware\SetLocale;
use CodeZero\LocalizedRoutes\Tests\Stubs\Model;
use CodeZero\LocalizedRoutes\Tests\Stubs\ModelWithCustomRouteParameters;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class LocalizedUrlMacroTest extends TestCase
{
    /** @test */
    public function it_generates_urls_with_default_localized_route_keys_for_the_current_route_using_route_model_binding()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        $model = (new Model([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('slug');

        App::instance(Model::class, $model);

        Route::localized(function () {
            Route::get('route/{first}/{second}', function (Model $first, Model $second) {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            })->middleware(['web']);
        });

        $response = $this->call('GET', '/en/route/en-slug/en-slug');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/en/route/en-slug/en-slug'),
            'en' => url('/en/route/en-slug/en-slug'),
            'nl' => url('/nl/route/nl-slug/nl-slug'),
        ], $response->original);
    }

    /** @test */
    public function it_generates_urls_with_custom_localized_route_keys_for_the_current_route_using_route_model_binding()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        $model = (new Model([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('id');

        App::instance(Model::class, $model);

        Route::localized(function () {
            Route::get('route/{model:slug}', function (Model $model) {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            })->middleware(['web']);
        });

        $response = $this->call('GET', '/en/route/en-slug');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/en/route/en-slug'),
            'en' => url('/en/route/en-slug'),
            'nl' => url('/nl/route/nl-slug'),
        ], $response->original);
    }

    /** @test */
    public function you_can_implement_an_interface_and_let_your_model_return_custom_parameters_with_route_model_binding()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        $model = (new ModelWithCustomRouteParameters([
            'id' => 1,
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('id');

        App::instance(ModelWithCustomRouteParameters::class, $model);

        Route::localized(function () {
            Route::get('route/{model}/{slug}', function (ModelWithCustomRouteParameters $model, $slug) {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            })->middleware(['web']);
        });

        $response = $this->call('GET', '/en/route/1/en-slug');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/en/route/1/en-slug'),
            'en' => url('/en/route/1/en-slug'),
            'nl' => url('/nl/route/1/nl-slug'),
        ], $response->original);
    }

    /** @test */
    public function it_cannot_guess_a_localized_route_key_without_route_model_binding()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        $model = (new Model([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('slug');

        App::instance(Model::class, $model);

        Route::localized(function () {
            Route::get('route/{slug}', function ($slug) {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            });
        });

        $response = $this->call('GET', '/en/route/en-slug');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/en/route/en-slug'),
            'en' => url('/en/route/en-slug'),
            'nl' => url('/nl/route/en-slug'), // Wrong slug!
        ], $response->original);
    }

    /** @test */
    public function you_can_pass_it_a_model_with_a_localized_route_key_without_route_model_binding()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        $model = (new Model([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('slug');

        App::instance(Model::class, $model);

        Route::localized(function () use ($model) {
            Route::get('route/{slug}', function ($slug) use ($model) {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en', [$model]),
                    'nl' => Route::localizedUrl('nl', [$model]),
                ];
            });
        });

        $response = $this->call('GET', '/en/route/en-slug');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/en/route/en-slug'),
            'en' => url('/en/route/en-slug'),
            'nl' => url('/nl/route/nl-slug'),
        ], $response->original);
    }

    /** @test */
    public function you_can_pass_it_a_closure_that_returns_the_parameters_without_route_model_binding()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        $model = (new Model([
            'id' => 1,
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('id');

        App::instance(Model::class, $model);

        Route::localized(function () use ($model) {
            Route::get('route/{id}/{slug}', function ($id, $slug) use ($model) {

                $closure = function ($locale) use ($model) {
                    return [$model->id, $model->getSlug($locale)];
                };

                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en', $closure),
                    'nl' => Route::localizedUrl('nl', $closure),
                ];
            });
        });

        $response = $this->call('GET', '/en/route/1/en-slug');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/en/route/1/en-slug'),
            'en' => url('/en/route/1/en-slug'),
            'nl' => url('/nl/route/1/nl-slug'),
        ], $response->original);
    }

    /** @test */
    public function it_handles_unnamed_non_localized_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        Route::get('route/one', function () {
            return [
                'current' => Route::localizedUrl(),
                'en' => Route::localizedUrl('en'),
                'nl' => Route::localizedUrl('nl'),
            ];
        });
        Route::get('route/two', function () {
            return [
                'current' => Route::localizedUrl(),
                'en' => Route::localizedUrl('en'),
                'nl' => Route::localizedUrl('nl'),
            ];
        });

        $response = $this->call('GET', '/route/one');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/route/one'),
            'en' => url('/route/one'),
            'nl' => url('/route/one'),
        ], $response->original);

        $response = $this->call('GET', '/route/two');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/route/two'),
            'en' => url('/route/two'),
            'nl' => url('/route/two'),
        ], $response->original);
    }

    /** @test */
    public function it_handles_unnamed_localized_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        Route::localized(function () {
            Route::get('route/one', function () {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            });
            Route::get('route/two', function () {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            });
        });

        $response = $this->call('GET', '/en/route/one');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/en/route/one'),
            'en' => url('/en/route/one'),
            'nl' => url('/nl/route/one'),
        ], $response->original);

        $response = $this->call('GET', '/en/route/two');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/en/route/two'),
            'en' => url('/en/route/two'),
            'nl' => url('/nl/route/two'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_the_current_url_for_existing_non_localized_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        Route::get('non/localized/route', function () {
            return [
                'current' => Route::localizedUrl(),
                'en' => Route::localizedUrl('en'),
                'nl' => Route::localizedUrl('nl'),
            ];
        })->name('non.localized.route');

        $response = $this->call('GET', '/non/localized/route');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/non/localized/route'),
            'en' => url('/non/localized/route'),
            'nl' => url('/non/localized/route'),
        ], $response->original);
    }

    /** @test */
    public function the_macro_does_not_blow_up_on_a_default_404_error()
    {
        // Although a default 404 has no Route::current() and is no real View, the composer still triggers.
        // Custom 404 views that trigger the macro still don't have a Route::current().
        View::composer('*', function ($view) {
            $view->with('url', Route::localizedUrl());
        });

        $response = $this->get('/en/route/does/not/exist');
        $response->assertNotFound();
        $response->assertResponseHasNoView();
    }

    /** @test */
    public function a_404_receives_the_correct_localized_url_from_a_view_composer()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');
        $this->setCustomErrorViewPath();

        View::composer('*', function ($view) {
            $view->with('response', Route::localizedUrl());
        });

        $response = $this->get('/nl/route/does/not/exist');
        $response->assertNotFound();
        $response->assertResponseHasNoView();
        $this->assertEquals(url('/nl/route/does/not/exist'), trim($response->original));
    }

    /** @test */
    public function a_404_is_not_localized_when_triggered_by_a_non_existing_route()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocaleMiddleware(true);
        $this->setAppLocale('en');
        $this->setCustomErrorViewPath();

        $response = $this->get('/nl/abort');
        $response->assertNotFound();
        $response->assertResponseHasNoView();
        $this->assertEquals('en', trim($response->original));
    }

    /** @test */
    public function a_404_is_localized_when_a_registered_route_throws_a_not_found_exception()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocaleMiddleware(true);
        $this->setAppLocale('en');
        $this->setCustomErrorViewPath();

        Route::localized(function () {
            Route::get('abort', function () {
                abort(404);
            });
        });

        $response = $this->get('/nl/abort');
        $response->assertNotFound();
        $response->assertResponseHasNoView();
        $this->assertEquals('nl', trim($response->original));
    }

    /** @test */
    public function a_404_is_localized_when_a_registered_route_throws_a_model_not_found_exception()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocaleMiddleware(true);
        $this->setAppLocale('en');
        $this->setCustomErrorViewPath();

        Route::localized(function () {
            Route::get('route/{model}', function ($model) {
                throw new ModelNotFoundException();
            });
        });

        $response = $this->get('/nl/route/mismatch');
        $response->assertNotFound();
        $response->assertResponseHasNoView();
        $this->assertEquals('nl', trim($response->original));
    }

    /** @test */
    public function a_fallback_route_is_not_triggered_when_a_registered_route_throws_a_not_found_exception()
    {
        Route::get('abort', function () {
            return abort(404);
        });

        Route::fallback(function () {
            return 'fallback';
        });

        $response = $this->get('/abort');
        $response->assertNotFound();
        $response->assertResponseHasNoView();
        $this->assertNotEquals('fallback', $response->original);
    }

    /** @test */
    public function a_fallback_route_is_not_triggered_when_a_registered_route_throws_a_model_not_found_exception()
    {
        Route::get('route/{model}', function ($model) {
            throw new ModelNotFoundException();
        });

        Route::fallback(function () {
            return 'fallback';
        });

        $response = $this->call('GET', '/route/mismatch');
        $response->assertNotFound();
        $response->assertResponseHasNoView();
        $this->assertNotEquals('fallback', $response->original);
    }

    /** @test */
    public function it_returns_a_localized_url_for_a_localized_fallback_route()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocaleMiddleware(true);

        Route::localized(function () {
            Route::fallback(function () {
                return response([
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ], 404);
            });
        });

        $response = $this->call('GET', '/nl/non/existing/route');
        $response->assertNotFound();
        $this->assertEquals([
            'current' => url('/nl/non/existing/route'),
            'en' => url('/en/non/existing/route'),
            'nl' => url('/nl/non/existing/route'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_a_localized_url_for_a_non_localized_fallback_route_if_the_url_contains_a_supported_locale()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::fallback(function () {
            return response([
                'current' => Route::localizedUrl(),
                'en' => Route::localizedUrl('en'),
                'nl' => Route::localizedUrl('nl'),
            ], 404);
        })->middleware(SetLocale::class);

        $response = $this->call('GET', '/nl/non/existing/route');
        $response->assertNotFound();
        $this->assertEquals([
            'current' => url('/nl/non/existing/route'),
            'en' => url('/en/non/existing/route'),
            'nl' => url('/nl/non/existing/route'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_a_localized_url_for_a_non_localized_fallback_route_if_the_url_does_not_contain_a_supported_locale()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('nl');

        Route::fallback(function () {
            return response([
                'current' => Route::localizedUrl(),
                'en' => Route::localizedUrl('en'),
                'nl' => Route::localizedUrl('nl'),
            ], 404);
        })->middleware(SetLocale::class);

        $response = $this->call('GET', '/non/existing/route');
        $response->assertNotFound();
        $this->assertEquals([
            'current' => url('/nl/non/existing/route'),
            'en' => url('/en/non/existing/route'),
            'nl' => url('/nl/non/existing/route'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_a_localized_url_for_a_non_localized_fallback_route_when_omitting_the_main_locale()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
        $this->setOmitUrlPrefixForLocale('nl');
        $this->setAppLocale('en');

        Route::fallback(function () {
            return response([
                'current' => Route::localizedUrl(),
                'en' => Route::localizedUrl('en'),
                'nl' => Route::localizedUrl('nl'),
            ], 404);
        })->middleware(SetLocale::class);

        $response = $this->call('GET', '/non/existing/route');
        $response->assertNotFound();
        $this->assertEquals([
            'current' => url('/non/existing/route'),
            'en' => url('/en/non/existing/route'),
            'nl' => url('/non/existing/route'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_a_localized_url_for_a_non_localized_fallback_route_when_using_custom_domains()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales([
            'en' => 'en.domain.test',
            'nl' => 'nl.domain.test',
        ]);
        $this->setAppLocale('en');

        Route::fallback(function () {
            return response([
                'current' => Route::localizedUrl(),
                'en' => Route::localizedUrl('en'),
                'nl' => Route::localizedUrl('nl'),
            ], 404);
        });

        $response = $this->call('GET', 'http://nl.domain.test/en/non/existing/route');
        $response->assertNotFound();
        $this->assertEquals([
            'current' => 'http://nl.domain.test/en/non/existing/route',
            'en' => 'http://en.domain.test/en/non/existing/route',
            'nl' => 'http://nl.domain.test/en/non/existing/route',
        ], $response->original);
    }

    /** @test */
    public function it_generates_non_absolute_urls_for_existing_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::get('route', function () {
            return [
                'current' => Route::localizedUrl(null, [], false),
                'en' => Route::localizedUrl('en', [], false),
                'nl' => Route::localizedUrl('nl', [], false),
            ];
        });

        $response = $this->call('GET', '/route');
        $response->assertOk();
        $this->assertEquals([
            'current' => '/route',
            'en' => '/route',
            'nl' => '/route',
        ], $response->original);
    }

    /** @test */
    public function it_generates_non_absolute_urls_for_non_existing_routes()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');
        $this->setCustomErrorViewPath();

        View::composer('*', function ($view) {
            $view->with('response', Route::localizedUrl(null, [], false));
        });

        $response = $this->get('/en/route/does/not/exist');
        $response->assertNotFound();
        $response->assertResponseHasNoView();
        $this->assertEquals('/en/route/does/not/exist', trim($response->original));
    }

    /** @test */
    public function it_returns_a_url_with_query_string_for_existing_non_localized_unnamed_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        Route::get('route', function () {
            return [
                'current' => Route::localizedUrl(),
                'en' => Route::localizedUrl('en'),
                'nl' => Route::localizedUrl('nl'),
            ];
        });

        $response = $this->call('GET', '/route?another=one&param=value');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/route?another=one&param=value'),
            'en' => url('/route?another=one&param=value'),
            'nl' => url('/route?another=one&param=value'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_a_url_with_query_string_for_existing_localized_unnamed_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocaleMiddleware(true);
        $this->setAppLocale('en');

        Route::localized(function () {
            Route::get('route', function () {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            });
        });

        $response = $this->call('GET', '/nl/route?another=one&param=value');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/nl/route?another=one&param=value'),
            'en' => url('/en/route?another=one&param=value'),
            'nl' => url('/nl/route?another=one&param=value'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_a_url_with_query_string_for_existing_non_localized_named_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        Route::get('route', function () {
            return [
                'current' => Route::localizedUrl(),
                'en' => Route::localizedUrl('en'),
                'nl' => Route::localizedUrl('nl'),
            ];
        })->name('route');

        $response = $this->call('GET', '/route?another=one&param=value');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/route?another=one&param=value'),
            'en' => url('/route?another=one&param=value'),
            'nl' => url('/route?another=one&param=value'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_a_url_with_query_string_for_existing_localized_named_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocaleMiddleware(true);
        $this->setAppLocale('en');

        Route::localized(function () {
            Route::get('route', function () {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            })->name('route');
        });

        $response = $this->call('GET', '/nl/route?another=one&param=value');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/nl/route?another=one&param=value'),
            'en' => url('/en/route?another=one&param=value'),
            'nl' => url('/nl/route?another=one&param=value'),
        ], $response->original);
    }

    /** @test */
    public function it_accepts_query_string_parameters_using_named_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        Route::get('route/{slug}/{optional?}', function () {
            return [
                'current' => Route::localizedUrl(null, ['another-slug', 'optional-slug', 'new' => 'value']),
                'en' => Route::localizedUrl('en', ['another-slug', 'optional-slug', 'new' => 'value']),
                'nl' => Route::localizedUrl('nl', ['another-slug', 'optional-slug', 'new' => 'value']),
            ];
        })->name('route');

        $response = $this->call('GET', '/route/some-slug?param=value');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/route/another-slug/optional-slug?new=value'),
            'en' => url('/route/another-slug/optional-slug?new=value'),
            'nl' => url('/route/another-slug/optional-slug?new=value'),
        ], $response->original);
    }

    /** @test */
    public function it_accepts_query_string_parameters_using_unnamed_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        Route::get('route/{slug}/{optional?}', function () {
            return [
                'current' => Route::localizedUrl(null, ['another-slug', 'optional-slug', 'new' => 'value']),
                'en' => Route::localizedUrl('en', ['another-slug', 'optional-slug', 'new' => 'value']),
                'nl' => Route::localizedUrl('nl', ['another-slug', 'optional-slug', 'new' => 'value']),
            ];
        });

        $response = $this->call('GET', '/route/some-slug?param=value');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/route/another-slug/optional-slug?new=value'),
            'en' => url('/route/another-slug/optional-slug?new=value'),
            'nl' => url('/route/another-slug/optional-slug?new=value'),
        ], $response->original);
    }

    /** @test */
    public function it_allows_optional_parameters_with_named_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        Route::get('route/{slug}/{one?}/{two?}', function () {
            return [
                'current' => Route::localizedUrl(null, ['another-slug']),
                'en' => Route::localizedUrl('en', ['another-slug']),
                'nl' => Route::localizedUrl('nl', ['another-slug']),
            ];
        })->name('route');

        $response = $this->call('GET', '/route/some-slug');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/route/another-slug'),
            'en' => url('/route/another-slug'),
            'nl' => url('/route/another-slug'),
        ], $response->original);
    }

    /** @test */
    public function it_allows_optional_parameters_with_unnamed_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        Route::get('route/{slug}/{one?}/{two?}', function () {
            return [
                'current' => Route::localizedUrl(null, ['another-slug']),
                'en' => Route::localizedUrl('en', ['another-slug']),
                'nl' => Route::localizedUrl('nl', ['another-slug']),
            ];
        });

        $response = $this->call('GET', '/route/some-slug');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/route/another-slug'),
            'en' => url('/route/another-slug'),
            'nl' => url('/route/another-slug'),
        ], $response->original);
    }

    /** @test */
    public function it_handles_capitalized_parameter_names()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        Route::get('route/{slugWithCaps}/{optionalSlugWithCaps?}', function () {
            return [
                'current' => Route::localizedUrl(null, ['another-slug']),
                'en' => Route::localizedUrl('en', ['another-slug']),
                'nl' => Route::localizedUrl('nl', ['another-slug']),
            ];
        });

        $response = $this->call('GET', '/route/some-slug');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/route/another-slug'),
            'en' => url('/route/another-slug'),
            'nl' => url('/route/another-slug'),
        ], $response->original);

    }

    /** @test */
    public function it_returns_a_url_with_translated_slugs_for_named_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocaleMiddleware(true);
        $this->setAppLocale('en');

        $this->setTranslations([
            'nl' => [
                'my-route' => 'nl-route',
            ],
            'en' => [
                'my-route' => 'en-route',
            ],
        ]);

        Route::localized(function () {
            Route::get(Lang::uri('route/my-route'), function () {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            })->name('route');
        });

        $response = $this->call('GET', '/nl/route/nl-route');
        $response->assertOk();
        $this->assertEquals([
            'current' => url('/nl/route/nl-route'),
            'en' => url('/en/route/en-route'),
            'nl' => url('/nl/route/nl-route'),
        ], $response->original);
    }

    /**
     * Set a custom view path so Laravel will find our custom 440 error view.
     *
     * @return void
     */
    protected function setCustomErrorViewPath()
    {
        Config::set('view.paths', [__DIR__ . '/../../Stubs/views']);
    }
}
