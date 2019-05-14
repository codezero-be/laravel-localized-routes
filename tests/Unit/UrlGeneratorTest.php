<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit;

use App;
use CodeZero\LocalizedRoutes\Tests\Stubs\Model;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use CodeZero\LocalizedRoutes\UrlGenerator;
use Config;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;
use Route;

class UrlGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('localized-routes.supported-locales', ['en', 'nl']);

        App::setLocale('en');
    }

    protected function registerRoute($url, $name, $callback = null)
    {
        Route::getRoutes()->add(
            Route::name($name)->get($url, $callback ?: function () {})
        );
    }

    /** @test */
    public function it_binds_our_custom_url_generator_class()
    {
        $this->assertInstanceOf(UrlGenerator::class, app('url'));
    }

    /** @test */
    public function it_gets_the_url_of_a_named_route()
    {
        $this->registerRoute('weirdly-named-route', 'en');
        $this->registerRoute('route', 'route');
        $this->registerRoute('en/route', 'en.route');
        $this->registerRoute('nl/route', 'nl.route');
        $this->registerRoute('route/name', 'route.name');
        $this->registerRoute('en/route/name', 'en.route.name');
        $this->registerRoute('nl/route/name', 'nl.route.name');

        $this->assertEquals(url('weirdly-named-route'), route('en'));
        $this->assertEquals(url('route'), route('route'));
        $this->assertEquals(url('en/route'), route('en.route'));
        $this->assertEquals(url('nl/route'), route('nl.route'));
        $this->assertEquals(url('route/name'), route('route.name'));
        $this->assertEquals(url('en/route/name'), route('en.route.name'));
        $this->assertEquals(url('nl/route/name'), route('nl.route.name'));
    }

    /** @test */
    public function it_gets_the_url_of_a_route_in_the_current_locale_if_the_given_route_name_does_not_exist()
    {
        $this->registerRoute('en/route', 'en.route.name');

        $this->assertEquals('en', App::getLocale());
        $this->assertEquals(url('en/route'), route('route.name'));
    }

    /** @test */
    public function it_throws_if_no_valid_route_can_be_found()
    {
        $this->registerRoute('wrong-route', 'wrong-route');

        $this->expectException(InvalidArgumentException::class);

        route('route');
    }

    /** @test */
    public function it_throws_if_no_valid_localized_route_can_be_found()
    {
        $this->registerRoute('nl/route', 'nl.route.name');

        $this->assertEquals('en', App::getLocale());
        $this->expectException(InvalidArgumentException::class);

        route('route.name');
    }

    /** @test */
    public function it_gets_the_url_of_a_route_in_the_given_locale()
    {
        $this->registerRoute('en/route', 'en.route.name');
        $this->registerRoute('nl/route', 'nl.route.name');

        $this->assertEquals('en', App::getLocale());
        $this->assertEquals(url('nl/route'), route('route.name', [], true, 'nl'));
        $this->assertEquals(url('nl/route'), route('en.route.name', [], true, 'nl'));
        $this->assertEquals(url('nl/route'), route('nl.route.name', [], true, 'nl'));
    }

    /** @test */
    public function it_always_gets_the_url_of_a_localized_route_if_a_locale_is_specified()
    {
        $this->registerRoute('route', 'route.name');
        $this->registerRoute('nl/route', 'nl.route.name');

        $this->assertEquals(url('nl/route'), route('route.name', [], true, 'nl'));
    }

    /** @test */
    public function it_throws_if_no_valid_route_can_be_found_for_the_given_locale()
    {
        $this->registerRoute('en/route', 'en.route.name');

        $this->assertEquals('en', App::getLocale());
        $this->expectException(InvalidArgumentException::class);

        route('en.route.name', [], true, 'nl');
    }

    /** @test */
    public function it_temporarily_changes_the_app_locale_when_generating_a_route_url()
    {
        $this->registerRoute('en/route/{slug}', 'en.route.name');
        $this->registerRoute('nl/route/{slug}', 'nl.route.name');

        App::setLocale('en');

        $this->assertEquals(url('en/route/en-slug'), route('route.name', [new Model]));
        $this->assertEquals(url('en/route/en-slug'), route('route.name', [new Model], true, 'en'));
        $this->assertEquals(url('nl/route/nl-slug'), route('route.name', [new Model], true, 'nl'));
    }

    /** @test */
    public function it_generates_a_signed_route_url_for_the_current_locale()
    {
        $callback = function () {
            return request()->hasValidSignature()
                ? 'Valid Signature'
                : 'Invalid Signature';
        };

        $this->registerRoute('en/route', 'en.route.name', $callback);
        $this->registerRoute('en/other/route', 'en.other.route.name', $callback);

        $validUrl = URL::signedRoute('route.name');
        $tamperedUrl = str_replace('en/route', 'en/other/route', $validUrl);

        $this->get($validUrl)->assertSee('Valid Signature');
        $this->get($tamperedUrl)->assertSee('Invalid Signature');
    }

    /** @test */
    public function it_generates_a_signed_route_url_for_a_specific_locale()
    {
        $callback = function () {
            return request()->hasValidSignature()
                ? 'Valid Signature'
                : 'Invalid Signature';
        };

        $this->registerRoute('en/route', 'en.route.name', $callback);
        $this->registerRoute('nl/route', 'nl.route.name', $callback);

        $validUrl = URL::signedRoute('route.name', [], null, true, 'nl');
        $tamperedUrl = str_replace('nl/route', 'en/route', $validUrl);

        $this->get($validUrl)->assertSee('Valid Signature');
        $this->get($tamperedUrl)->assertSee('Invalid Signature');
    }
}
