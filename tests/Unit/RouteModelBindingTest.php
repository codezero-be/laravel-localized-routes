<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use CodeZero\LocalizedRoutes\Tests\Stubs\Model;

class RouteModelBindingTest extends TestCase
{
    /** @test */
    public function it_loads_a_route_with_a_localized_route_key_based_on_the_active_locale()
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

        Route::middleware(['web'])->get('test/{model}', function (Model $model) {});

        Route::localized(function () {
            Route::middleware(['web'])->get('test/{model}', function (Model $model) {});
        });

        $this->setAppLocale('nl');

        $this->get('test/nl-slug')->assertOk();
        $this->get('test/en-slug')->assertNotFound();

        $this->get('nl/test/nl-slug')->assertOk();
        $this->get('nl/test/en-slug')->assertNotFound();

        $this->get('en/test/en-slug')->assertOk();
        $this->get('en/test/nl-slug')->assertNotFound();
    }

    /** @test */
    public function it_loads_a_route_with_a_custom_localized_route_key_based_on_the_active_locale()
    {
        if (App::version() < 7) {
            $this->markTestSkipped('This feature is only available in Laravel 7 and newer.');
        }

        $this->setSupportedLocales(['en', 'nl']);
        $this->setUseLocaleMiddleware(true);

        $model = (new Model([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('id');

        App::instance(Model::class, $model);

        Route::middleware(['web'])->get('test/{model:slug}', function (Model $model) {});

        Route::localized(function () {
            Route::middleware(['web'])->get('test/{model:slug}', function (Model $model) {});
        });

        $this->setAppLocale('nl');

        $this->get('test/nl-slug')->assertOk();
        $this->get('test/en-slug')->assertNotFound();

        $this->get('nl/test/nl-slug')->assertOk();
        $this->get('nl/test/en-slug')->assertNotFound();

        $this->get('en/test/en-slug')->assertOk();
        $this->get('en/test/nl-slug')->assertNotFound();
    }
}
