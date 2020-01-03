<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit\Macros;

use CodeZero\LocalizedRoutes\Tests\Stubs\Model;
use CodeZero\LocalizedRoutes\Tests\Stubs\ModelWithCustomRouteParameters;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

class LocalizedUrlMacroTest extends TestCase
{
    /** @test */
    public function it_generates_urls_with_localized_route_keys_for_the_current_route_using_route_model_binding()
    {
        $this->setSupportedLocales(['en', 'nl']);

        $model = (new Model([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('slug');

        App::instance(Model::class, $model);

        Route::localized(function () {
            Route::get('route/{model}', function (Model $model) {
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
}
