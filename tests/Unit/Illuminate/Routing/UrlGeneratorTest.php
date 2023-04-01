<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit\Illuminate\Routing;

use CodeZero\LocalizedRoutes\Tests\Stubs\Controller;
use CodeZero\LocalizedRoutes\Tests\Stubs\Models\ModelOneWithRouteBinding;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use CodeZero\LocalizedRoutes\Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class UrlGeneratorTest extends TestCase
{
    /** @test */
    public function it_binds_our_custom_url_generator_class()
    {
        $this->assertInstanceOf(UrlGenerator::class, App::make('url'));
        $this->assertInstanceOf(UrlGenerator::class, App::make('redirect')->getUrlGenerator());
    }

    /** @test */
    public function it_gets_the_url_of_a_named_route_as_usual()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::get('weirdly-named-route')->name('en');
        Route::get('route')->name('route');
        Route::get('en/route')->name('en.route');
        Route::get('nl/route')->name('nl.route');
        Route::get('route/name')->name('route.name');
        Route::get('en/route/name')->name('en.route.name');
        Route::get('nl/route/name')->name('nl.route.name');

        $this->assertEquals(URL::to('weirdly-named-route'), URL::route('en'));
        $this->assertEquals(URL::to('route'), URL::route('route'));
        $this->assertEquals(URL::to('en/route'), URL::route('en.route'));
        $this->assertEquals(URL::to('nl/route'), URL::route('nl.route'));
        $this->assertEquals(URL::to('route/name'), URL::route('route.name'));
        $this->assertEquals(URL::to('en/route/name'), URL::route('en.route.name'));
        $this->assertEquals(URL::to('nl/route/name'), URL::route('nl.route.name'));
    }

    /** @test */
    public function it_gets_the_url_of_a_route_in_the_current_locale_if_the_given_route_name_does_not_exist()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::get('en/route')->name('en.route.name');

        $this->assertEquals(URL::to('en/route'), URL::route('route.name'));
    }

    /** @test */
    public function it_throws_if_no_valid_route_can_be_found()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::get('wrong-route')->name('wrong-route');

        $this->expectException(InvalidArgumentException::class);

        URL::route('route');
    }

    /** @test */
    public function it_throws_if_no_valid_localized_route_can_be_found()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::get('nl/route')->name('nl.route.name');

        $this->expectException(InvalidArgumentException::class);

        URL::route('route.name');
    }

    /** @test */
    public function it_gets_the_url_of_a_route_in_the_given_locale()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::get('en/route')->name('en.route.name');
        Route::get('nl/route')->name('nl.route.name');

        $this->assertEquals(URL::to('nl/route'), URL::route('route.name', [], true, 'nl'));
        $this->assertEquals(URL::to('nl/route'), URL::route('en.route.name', [], true, 'nl'));
        $this->assertEquals(URL::to('nl/route'), URL::route('nl.route.name', [], true, 'nl'));
    }

    /** @test */
    public function it_gets_the_url_of_a_route_in_the_given_locale_when_using_custom_domains()
    {
        $this->setSupportedLocales([
            'en' => 'en.domain.test',
            'nl' => 'nl.domain.test',
        ]);
        $this->setAppLocale('en');

        Route::get('route')->name('en.route.name')->domain('en.domain.test');
        Route::get('route')->name('nl.route.name')->domain('nl.domain.test');

        $this->assertEquals('http://nl.domain.test/route', URL::route('route.name', [], true, 'nl'));
        $this->assertEquals('http://nl.domain.test/route', URL::route('en.route.name', [], true, 'nl'));
        $this->assertEquals('http://nl.domain.test/route', URL::route('nl.route.name', [], true, 'nl'));
    }

    /** @test */
    public function it_gets_the_url_of_a_route_in_the_given_locale_when_using_custom_slugs()
    {
        $this->setSupportedLocales([
            'en' => 'english',
            'nl' => 'dutch',
        ]);

        Route::get('english/route')->name('en.route.name');
        Route::get('dutch/route')->name('nl.route.name');

        $this->assertEquals(URL::to('dutch/route'), URL::route('route.name', [], true, 'nl'));
        $this->assertEquals(URL::to('dutch/route'), URL::route('en.route.name', [], true, 'nl'));
        $this->assertEquals(URL::to('dutch/route'), URL::route('nl.route.name', [], true, 'nl'));
    }

    /** @test */
    public function it_always_gets_the_url_of_a_localized_route_if_a_locale_is_specified()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::get('route')->name('route.name');
        Route::get('nl/route')->name('nl.route.name');

        $this->assertEquals(URL::to('nl/route'), URL::route('route.name', [], true, 'nl'));
    }

    /** @test */
    public function it_returns_a_registered_non_localized_url_if_a_localized_version_does_not_exist()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::get('route')->name('route.name');
        Route::get('nl/route')->name('nl.route.name');

        $this->assertEquals(URL::to('route'), URL::route('route.name', [], true, 'en'));
    }

    /** @test */
    public function it_throws_if_no_valid_route_can_be_found_for_the_given_locale()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::get('en/route')->name('en.route.name');

        $this->expectException(RouteNotFoundException::class);

        URL::route('en.route.name', [], true, 'nl');
    }

    /** @test */
    function it_uses_a_fallback_locale_when_the_requested_locale_is_unsupported()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');
        $this->setFallbackLocale('en');

        Route::get('en/route')->name('en.route');
        Route::get('nl/route')->name('nl.route');

        $this->assertEquals(URL::to('en/route'), URL::route('route', [], true, 'en'));
        $this->assertEquals(URL::to('nl/route'), URL::route('route', [], true, 'nl'));
        $this->assertEquals(URL::to('en/route'), URL::route('route', [], true, 'fr'));
    }

    /** @test */
    public function it_uses_a_fallback_locale_when_the_requested_locale_is_not_registered()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');
        $this->setFallbackLocale('en');

        Route::get('en/route')->name('en.route');

        $this->assertEquals(URL::to('en/route'), URL::route('route', [], true, 'en'));
        $this->assertEquals(URL::to('en/route'), URL::route('route', [], true, 'nl'));
    }

    /** @test */
    public function it_throws_if_you_do_not_specify_a_name_for_a_localized_route()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::get('en/route')->name('en.');

        $this->expectException(RouteNotFoundException::class);

        URL::route('en.', [], true, 'en');
    }

    /** @test */
    public function it_generates_a_url_for_a_route_with_a_default_localized_route_key()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $model = (new ModelOneWithRouteBinding([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('slug');

        App::instance(ModelOneWithRouteBinding::class, $model);

        Route::get('en/route/{slug}')->name('en.route.name');
        Route::get('nl/route/{slug}')->name('nl.route.name');

        $this->assertEquals(URL::to('en/route/en-slug'), URL::route('route.name', [$model]));
        $this->assertEquals(URL::to('en/route/en-slug'), URL::route('route.name', [$model], true, 'en'));
        $this->assertEquals(URL::to('nl/route/nl-slug'), URL::route('route.name', [$model], true, 'nl'));
    }

    /** @test */
    public function it_generates_a_url_for_a_route_with_a_custom_localized_route_key()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $model = (new ModelOneWithRouteBinding([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('id');

        App::instance(ModelOneWithRouteBinding::class, $model);

        Route::get('en/route/{model:slug}')->name('en.route.name');
        Route::get('nl/route/{model:slug}')->name('nl.route.name');

        $this->assertEquals(URL::to('en/route/en-slug'), URL::route('route.name', [$model]));
        $this->assertEquals(URL::to('en/route/en-slug'), URL::route('route.name', [$model], true, 'en'));
        $this->assertEquals(URL::to('nl/route/nl-slug'), URL::route('route.name', [$model], true, 'nl'));
    }

    /** @test */
    public function it_generates_a_signed_route_url_for_the_current_locale()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $callback = function () {
            return Request::hasValidSignature()
                ? 'Valid Signature'
                : 'Invalid Signature';
        };

        Route::get('en/route', $callback)->name('en.route.name');
        Route::get('en/other/route', $callback)->name('en.other.route.name');

        $validUrl = URL::signedRoute('route.name');
        $tamperedUrl = str_replace('en/route', 'en/other/route', $validUrl);

        $this->get($validUrl)->assertSee('Valid Signature');
        $this->get($tamperedUrl)->assertSee('Invalid Signature');
    }

    /** @test */
    public function it_generates_a_signed_route_url_for_a_specific_locale()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $callback = function () {
            return Request::hasValidSignature()
                ? 'Valid Signature'
                : 'Invalid Signature';
        };

        Route::get('en/route', $callback)->name('en.route.name');
        Route::get('nl/route', $callback)->name('nl.route.name');

        $validUrl = URL::signedRoute('route.name', [], null, true, 'nl');
        $tamperedUrl = str_replace('nl/route', 'en/route', $validUrl);

        $this->get($validUrl)->assertSee('Valid Signature');
        $this->get($tamperedUrl)->assertSee('Invalid Signature');
    }

    /** @test */
    public function it_allows_routes_to_be_cached()
    {
        $this->withoutExceptionHandling();
        $this->setSupportedLocales(['en']);
        $this->setAppLocale('en');

        Route::get('en/route', [Controller::class, 'index']);

        $this->cacheRoutes();

        $this->get('en/route')->assertSuccessful();
    }

    /**
     * Cache registered routes.
     *
     * @return void
     */
    protected function cacheRoutes()
    {
        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            $route->prepareForSerialization();
        }

        $this->app['router']->setCompiledRoutes(
            $routes->compile()
        );
    }
}
