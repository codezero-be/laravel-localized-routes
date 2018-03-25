<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit;

use CodeZero\LocalizedRoutes\Tests\Stubs\Model;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use CodeZero\LocalizedRoutes\UrlGenerator;
use InvalidArgumentException;
use Route;

class UrlGeneratorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        config()->set('app.locales', ['en', 'nl']);

        app()->setLocale('en');
    }

    protected function registerRoute($url, $name)
    {
        Route::getRoutes()->add(
            Route::name($name)->get($url, function () use ($name) { return $name; })
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
        $this->registerRoute('route', 'route.name');
        $this->registerRoute('en/route', 'en.route.name');
        $this->registerRoute('nl/route', 'nl.route.name');

        $this->assertEquals(url('route'), route('route.name'));
        $this->assertEquals(url('en/route'), route('en.route.name'));
        $this->assertEquals(url('nl/route'), route('nl.route.name'));
    }

    /** @test */
    public function it_gets_the_url_of_a_route_in_the_current_locale_if_the_given_route_name_does_not_exist()
    {
        $this->registerRoute('en/route', 'en.route.name');

        $this->assertEquals('en', app()->getLocale());
        $this->assertEquals(url('en/route'), route('route.name'));
    }

    /** @test */
    public function it_throws_if_no_valid_route_can_be_found()
    {
        $this->registerRoute('nl/route', 'nl.route.name');

        $this->assertEquals('en', app()->getLocale());
        $this->expectException(InvalidArgumentException::class);

        route('route.name');
    }

    /** @test */
    public function it_gets_the_url_of_a_route_in_the_given_locale()
    {
        $this->registerRoute('en/route', 'en.route.name');
        $this->registerRoute('nl/route', 'nl.route.name');

        $this->assertEquals('en', app()->getLocale());
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

        $this->assertEquals('en', app()->getLocale());
        $this->expectException(InvalidArgumentException::class);

        route('en.route.name', [], true, 'nl');
    }

    /** @test */
    public function it_temporarily_changes_the_app_locale_when_generating_a_route_url()
    {
        $this->registerRoute('en/route/{slug}', 'en.route.name');
        $this->registerRoute('nl/route/{slug}', 'nl.route.name');

        app()->setLocale('en');

        $this->assertEquals(url('en/route/en-slug'), route('route.name', [new Model]));
        $this->assertEquals(url('en/route/en-slug'), route('route.name', [new Model], true, 'en'));
        $this->assertEquals(url('nl/route/nl-slug'), route('route.name', [new Model], true, 'nl'));
    }
}
