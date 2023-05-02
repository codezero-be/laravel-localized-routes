<?php

namespace CodeZero\LocalizedRoutes\Tests\Feature\Macros\Route;

use CodeZero\LocalizedRoutes\Middleware\SetLocale;
use CodeZero\LocalizedRoutes\Tests\Stubs\Models\ModelOneWithRouteBinding;
use CodeZero\LocalizedRoutes\Tests\Stubs\Models\ModelTwoWithRouteBinding;
use CodeZero\LocalizedRoutes\Tests\Stubs\Models\ModelWithMultipleRouteParameters;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class LocalizedUrlMacroTest extends TestCase
{
    /** @test */
    public function it_generates_urls_with_default_localized_route_keys_for_the_current_route_using_route_model_binding()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        $model = (new ModelOneWithRouteBinding([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('slug');

        App::instance(ModelOneWithRouteBinding::class, $model);

        Route::localized(function () {
            Route::get('route/{first}/{second}', function (ModelOneWithRouteBinding $first, ModelOneWithRouteBinding $second) {
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
            'current' => URL::to('/en/route/en-slug/en-slug'),
            'en' => URL::to('/en/route/en-slug/en-slug'),
            'nl' => URL::to('/nl/route/nl-slug/nl-slug'),
        ], $response->original);
    }

    /** @test */
    public function it_generates_urls_for_the_current_route_with_different_models_using_route_model_binding()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        $foo = (new ModelOneWithRouteBinding([
            'slug' => [
                'en' => 'en-slug-foo',
                'nl' => 'nl-slug-foo',
            ],
        ]))->setKeyName('slug');

        $bar = (new ModelTwoWithRouteBinding([
            'slug' => [
                'en' => 'en-slug-bar',
                'nl' => 'nl-slug-bar',
            ],
        ]))->setKeyName('slug');

        App::instance(ModelOneWithRouteBinding::class, $foo);
        App::instance(ModelTwoWithRouteBinding::class, $bar);

        Route::localized(function () {
            Route::get('route/{foo}/{bar}', function (ModelOneWithRouteBinding $foo, ModelTwoWithRouteBinding $bar) {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            })->middleware(['web']);
        });

        $response = $this->call('GET', '/en/route/en-slug-foo/en-slug-bar');
        $response->assertOk();
        $this->assertEquals([
            'current' => URL::to('/en/route/en-slug-foo/en-slug-bar'),
            'en' => URL::to('/en/route/en-slug-foo/en-slug-bar'),
            'nl' => URL::to('/nl/route/nl-slug-foo/nl-slug-bar'),
        ], $response->original);
    }

    /** @test */
    public function it_generates_urls_with_custom_localized_route_keys_for_the_current_route_using_route_model_binding()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        $model = (new ModelOneWithRouteBinding([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('id');

        App::instance(ModelOneWithRouteBinding::class, $model);

        Route::localized(function () {
            Route::get('route/{model:slug}', function (ModelOneWithRouteBinding $model) {
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
            'current' => URL::to('/en/route/en-slug'),
            'en' => URL::to('/en/route/en-slug'),
            'nl' => URL::to('/nl/route/nl-slug'),
        ], $response->original);
    }

    /** @test */
    public function you_can_implement_an_interface_and_let_your_model_return_custom_parameters_with_route_model_binding()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        $model = (new ModelWithMultipleRouteParameters([
            'id' => 1,
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('id');

        App::instance(ModelWithMultipleRouteParameters::class, $model);

        Route::localized(function () {
            Route::get('route/{model}/{slug}', function (ModelWithMultipleRouteParameters $model, $slug) {
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
            'current' => URL::to('/en/route/1/en-slug'),
            'en' => URL::to('/en/route/1/en-slug'),
            'nl' => URL::to('/nl/route/1/nl-slug'),
        ], $response->original);
    }

    /** @test */
    public function it_cannot_guess_a_localized_route_key_without_route_model_binding()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        $model = (new ModelOneWithRouteBinding([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('slug');

        App::instance(ModelOneWithRouteBinding::class, $model);

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
            'current' => URL::to('/en/route/en-slug'),
            'en' => URL::to('/en/route/en-slug'),
            'nl' => URL::to('/nl/route/en-slug'), // Wrong slug!
        ], $response->original);
    }

    /** @test */
    public function you_can_pass_it_a_model_with_a_localized_route_key_without_route_model_binding()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        $model = (new ModelOneWithRouteBinding([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('slug');

        App::instance(ModelOneWithRouteBinding::class, $model);

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
            'current' => URL::to('/en/route/en-slug'),
            'en' => URL::to('/en/route/en-slug'),
            'nl' => URL::to('/nl/route/nl-slug'),
        ], $response->original);
    }

    /** @test */
    public function you_can_pass_it_a_closure_that_returns_the_parameters_without_route_model_binding()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        $model = (new ModelOneWithRouteBinding([
            'id' => 1,
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('id');

        App::instance(ModelOneWithRouteBinding::class, $model);

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
            'current' => URL::to('/en/route/1/en-slug'),
            'en' => URL::to('/en/route/1/en-slug'),
            'nl' => URL::to('/nl/route/1/nl-slug'),
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
            'current' => URL::to('/route/one'),
            'en' => URL::to('/route/one'),
            'nl' => URL::to('/route/one'),
        ], $response->original);

        $response = $this->call('GET', '/route/two');
        $response->assertOk();
        $this->assertEquals([
            'current' => URL::to('/route/two'),
            'en' => URL::to('/route/two'),
            'nl' => URL::to('/route/two'),
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
            'current' => URL::to('/en/route/one'),
            'en' => URL::to('/en/route/one'),
            'nl' => URL::to('/nl/route/one'),
        ], $response->original);

        $response = $this->call('GET', '/en/route/two');
        $response->assertOk();
        $this->assertEquals([
            'current' => URL::to('/en/route/two'),
            'en' => URL::to('/en/route/two'),
            'nl' => URL::to('/nl/route/two'),
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
            'current' => URL::to('/non/localized/route'),
            'en' => URL::to('/non/localized/route'),
            'nl' => URL::to('/non/localized/route'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_the_url_for_existing_unnamed_localized_routes_using_custom_slugs()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales([
            'en' => 'english',
            'nl' => 'dutch',
        ]);
        $this->setAppLocale('en');

        Route::localized(function () {
            Route::get('/', function () {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            });
        });

        $response = $this->call('GET', '/english');
        $response->assertOk();
        $this->assertEquals([
            'current' => URL::to('/english'),
            'en' => URL::to('/english'),
            'nl' => URL::to('/dutch'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_the_url_for_existing_named_localized_routes_using_custom_slugs()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales([
            'en' => 'english',
            'nl' => 'dutch',
        ]);
        $this->setAppLocale('en');

        Route::localized(function () {
            Route::get('/', function () {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            })->name('route');
        });

        $response = $this->call('GET', '/english');
        $response->assertOk();
        $this->assertEquals([
            'current' => URL::to('/english'),
            'en' => URL::to('/english'),
            'nl' => URL::to('/dutch'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_the_url_for_existing_unnamed_localized_routes_using_domains()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales([
            'en' => 'domain.test',
            'nl' => 'nl.domain.test',
        ]);
        $this->setAppLocale('en');
        $this->setFallbackLocale('en');

        Route::localized(function () {
            Route::get('/', function () {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            });
        });

        $response = $this->call('GET', 'http://domain.test');
        $response->assertOk();
        $this->assertEquals([
            'current' => 'http://domain.test',
            'en' => 'http://domain.test',
            'nl' => 'http://nl.domain.test',
        ], $response->original);
    }

    /** @test */
    public function it_returns_the_url_for_existing_named_localized_routes_using_domains()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales([
            'en' => 'domain.test',
            'nl' => 'nl.domain.test',
        ]);
        $this->setAppLocale('en');
        $this->setFallbackLocale('en');

        Route::localized(function () {
            Route::get('/', function () {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            })->name('route');
        });

        $response = $this->call('GET', 'http://domain.test');
        $response->assertOk();
        $this->assertEquals([
            'current' => 'http://domain.test',
            'en' => 'http://domain.test',
            'nl' => 'http://nl.domain.test',
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
        $this->assertEquals(URL::to('/nl/route/does/not/exist'), trim($response->original));
    }

    /** @test */
    public function a_404_is_not_localized_when_triggered_by_a_non_existing_route()
    {
        $this->setSupportedLocales(['en', 'nl']);
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
        $this->setAppLocale('en');
        $this->setCustomErrorViewPath();

        Route::localized(function () {
            Route::get('abort', function () {
                abort(404);
            })->middleware(['web', SetLocale::class]);
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
        $this->setAppLocale('en');
        $this->setCustomErrorViewPath();

        Route::localized(function () {
            Route::get('route/{model}', function ($model) {
                throw new ModelNotFoundException();
            })->middleware(['web', SetLocale::class]);
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
        $this->setAppLocale('en');

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
            'current' => URL::to('/nl/non/existing/route'),
            'en' => URL::to('/en/non/existing/route'),
            'nl' => URL::to('/nl/non/existing/route'),
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
        });

        $response = $this->call('GET', '/nl/non/existing/route');
        $response->assertNotFound();
        $this->assertEquals([
            'current' => URL::to('/nl/non/existing/route'),
            'en' => URL::to('/en/non/existing/route'),
            'nl' => URL::to('/nl/non/existing/route'),
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
        });

        $response = $this->call('GET', '/non/existing/route');
        $response->assertNotFound();
        $this->assertEquals([
            'current' => URL::to('/nl/non/existing/route'),
            'en' => URL::to('/en/non/existing/route'),
            'nl' => URL::to('/nl/non/existing/route'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_a_localized_url_for_a_non_localized_fallback_route_when_omitting_the_main_locale()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
        $this->setOmittedLocale('nl');
        $this->setAppLocale('en');

        Route::fallback(function () {
            return response([
                'current' => Route::localizedUrl(),
                'en' => Route::localizedUrl('en'),
                'nl' => Route::localizedUrl('nl'),
            ], 404);
        })->middleware(['web', SetLocale::class]);

        $response = $this->call('GET', '/non/existing/route');
        $response->assertNotFound();
        $this->assertEquals([
            'current' => URL::to('/non/existing/route'),
            'en' => URL::to('/en/non/existing/route'),
            'nl' => URL::to('/non/existing/route'),
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
            'current' => URL::to('/route?another=one&param=value'),
            'en' => URL::to('/route?another=one&param=value'),
            'nl' => URL::to('/route?another=one&param=value'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_a_url_with_query_string_for_existing_localized_unnamed_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
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
            'current' => URL::to('/nl/route?another=one&param=value'),
            'en' => URL::to('/en/route?another=one&param=value'),
            'nl' => URL::to('/nl/route?another=one&param=value'),
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
            'current' => URL::to('/route?another=one&param=value'),
            'en' => URL::to('/route?another=one&param=value'),
            'nl' => URL::to('/route?another=one&param=value'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_a_url_with_query_string_for_existing_localized_named_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
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
            'current' => URL::to('/nl/route?another=one&param=value'),
            'en' => URL::to('/en/route?another=one&param=value'),
            'nl' => URL::to('/nl/route?another=one&param=value'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_a_url_without_query_string_for_existing_localized_named_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::localized(function () {
            Route::get('route', function () {
                return [
                    'current' => Route::localizedUrl(null, null, true, $keepQuery = false),
                    'en' => Route::localizedUrl('en', null, true, $keepQuery = false),
                    'nl' => Route::localizedUrl('nl', null, true, $keepQuery = false),
                ];
            })->name('route');
        });

        $response = $this->call('GET', '/nl/route?another=one&param=value');
        $response->assertOk();
        $this->assertEquals([
            'current' => URL::to('/nl/route'),
            'en' => URL::to('/en/route'),
            'nl' => URL::to('/nl/route'),
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
            'current' => URL::to('/route/another-slug/optional-slug?new=value'),
            'en' => URL::to('/route/another-slug/optional-slug?new=value'),
            'nl' => URL::to('/route/another-slug/optional-slug?new=value'),
        ], $response->original);
    }

    /** @test */
    public function it_ignores_query_string_parameters_using_named_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        Route::get('route/{slug}/{optional?}', function () {
            return [
                'current' => Route::localizedUrl(null, ['another-slug', 'optional-slug', 'new' => 'value'], true, $keepQuery = false),
                'en' => Route::localizedUrl('en', ['another-slug', 'optional-slug', 'new' => 'value'], true, $keepQuery = false),
                'nl' => Route::localizedUrl('nl', ['another-slug', 'optional-slug', 'new' => 'value'], true, $keepQuery = false),
            ];
        })->name('route');

        $response = $this->call('GET', '/route/some-slug?param=value');
        $response->assertOk();
        $this->assertEquals([
            'current' => URL::to('/route/another-slug/optional-slug'),
            'en' => URL::to('/route/another-slug/optional-slug'),
            'nl' => URL::to('/route/another-slug/optional-slug'),
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
            'current' => URL::to('/route/another-slug/optional-slug?new=value'),
            'en' => URL::to('/route/another-slug/optional-slug?new=value'),
            'nl' => URL::to('/route/another-slug/optional-slug?new=value'),
        ], $response->original);
    }

    /** @test */
    public function it_ignores_query_string_parameters_using_unnamed_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        Route::get('route/{slug}/{optional?}', function () {
            return [
                'current' => Route::localizedUrl(null, ['another-slug', 'optional-slug', 'new' => 'value'], true, $keepQuery = false),
                'en' => Route::localizedUrl('en', ['another-slug', 'optional-slug', 'new' => 'value'], true, $keepQuery = false),
                'nl' => Route::localizedUrl('nl', ['another-slug', 'optional-slug', 'new' => 'value'], true, $keepQuery = false),
            ];
        });

        $response = $this->call('GET', '/route/some-slug?param=value');
        $response->assertOk();
        $this->assertEquals([
            'current' => URL::to('/route/another-slug/optional-slug'),
            'en' => URL::to('/route/another-slug/optional-slug'),
            'nl' => URL::to('/route/another-slug/optional-slug'),
        ], $response->original);
    }

    /** @test */
    public function it_prefers_route_parameters_before_query_string_parameters_with_the_same_name_in_unnamed_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        $model = (new ModelOneWithRouteBinding([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('slug');

        App::instance(ModelOneWithRouteBinding::class, $model);

        Route::localized(function () use ($model) {
            Route::get('route/{slug}', function (ModelOneWithRouteBinding $slug) {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            })->middleware(['web']);
        });

        $response = $this->call('GET', '/en/route/en-slug?slug=duplicate');
        $response->assertOk();
        $this->assertEquals([
            'current' => URL::to('/en/route/en-slug?slug=duplicate'),
            'en' => URL::to('/en/route/en-slug?slug=duplicate'),
            'nl' => URL::to('/nl/route/nl-slug?slug=duplicate'),
        ], $response->original);
    }

    /** @test */
    public function it_prefers_route_parameters_before_query_string_parameters_with_the_same_name_in_named_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);

        $model = (new ModelOneWithRouteBinding([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('slug');

        App::instance(ModelOneWithRouteBinding::class, $model);

        Route::localized(function () use ($model) {
            Route::get('route/{slug}', function (ModelOneWithRouteBinding $slug) {
                return [
                    'current' => Route::localizedUrl(),
                    'en' => Route::localizedUrl('en'),
                    'nl' => Route::localizedUrl('nl'),
                ];
            })->middleware(['web'])->name('test');
        });

        $response = $this->call('GET', '/en/route/en-slug?slug=duplicate');
        $response->assertOk();
        $this->assertEquals([
            'current' => URL::to('/en/route/en-slug?slug=duplicate'),
            'en' => URL::to('/en/route/en-slug?slug=duplicate'),
            'nl' => URL::to('/nl/route/nl-slug?slug=duplicate'),
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
            'current' => URL::to('/route/another-slug'),
            'en' => URL::to('/route/another-slug'),
            'nl' => URL::to('/route/another-slug'),
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
            'current' => URL::to('/route/another-slug'),
            'en' => URL::to('/route/another-slug'),
            'nl' => URL::to('/route/another-slug'),
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
            'current' => URL::to('/route/another-slug'),
            'en' => URL::to('/route/another-slug'),
            'nl' => URL::to('/route/another-slug'),
        ], $response->original);
    }

    /** @test */
    public function it_returns_a_url_with_translated_slugs_for_named_routes()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en', 'nl']);
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
            'current' => URL::to('/nl/route/nl-route'),
            'en' => URL::to('/en/route/en-route'),
            'nl' => URL::to('/nl/route/nl-route'),
        ], $response->original);
    }

    /**
     * Set a custom view path so Laravel will find our custom 440 error view.
     *
     * @return void
     */
    protected function setCustomErrorViewPath()
    {
        Config::set('view.paths', [__DIR__ . '/../../../Stubs/views']);
    }
}
