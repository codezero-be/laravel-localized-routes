<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit;

use CodeZero\LocalizedRoutes\Tests\Stubs\Model;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use CodeZero\LocalizedRoutes\UrlGenerator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;

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
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $this->registerRoute('en/route', 'en.route.name');

        $this->assertEquals(url('en/route'), route('route.name'));
    }

    /** @test */
    public function it_throws_if_no_valid_route_can_be_found()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $this->registerRoute('wrong-route', 'wrong-route');

        $this->expectException(InvalidArgumentException::class);

        route('route');
    }

    /** @test */
    public function it_throws_if_no_valid_localized_route_can_be_found()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $this->registerRoute('nl/route', 'nl.route.name');

        $this->expectException(InvalidArgumentException::class);

        route('route.name');
    }

    /** @test */
    public function it_gets_the_url_of_a_route_in_the_given_locale()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $this->registerRoute('en/route', 'en.route.name');
        $this->registerRoute('nl/route', 'nl.route.name');

        $this->assertEquals(url('nl/route'), route('route.name', [], true, 'nl'));
        $this->assertEquals(url('nl/route'), route('en.route.name', [], true, 'nl'));
        $this->assertEquals(url('nl/route'), route('nl.route.name', [], true, 'nl'));
    }

    /** @test */
    public function it_gets_the_url_of_a_route_in_the_given_locale_when_using_custom_domains()
    {
        $this->setSupportedLocales([
            'en' => 'en.domain.test',
            'nl' => 'nl.domain.test',
        ]);
        $this->setAppLocale('en');

        $this->registerRoute('route', 'en.route.name')->domain('en.domain.test');
        $this->registerRoute('route', 'nl.route.name')->domain('nl.domain.test');

        $this->assertEquals('http://nl.domain.test/route', route('route.name', [], true, 'nl'));
        $this->assertEquals('http://nl.domain.test/route', route('en.route.name', [], true, 'nl'));
        $this->assertEquals('http://nl.domain.test/route', route('nl.route.name', [], true, 'nl'));
    }

    /** @test */
    public function it_always_gets_the_url_of_a_localized_route_if_a_locale_is_specified()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $this->registerRoute('route', 'route.name');
        $this->registerRoute('nl/route', 'nl.route.name');

        $this->assertEquals(url('nl/route'), route('route.name', [], true, 'nl'));
    }

    /** @test */
    public function it_throws_if_no_valid_route_can_be_found_for_the_given_locale()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $this->registerRoute('en/route', 'en.route.name');

        $this->expectException(InvalidArgumentException::class);

        route('en.route.name', [], true, 'nl');
    }

    /** @test */
    public function it_temporarily_changes_the_app_locale_when_generating_a_route_url()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $model = (new Model([
            'slug' => [
                'en' => 'en-slug',
                'nl' => 'nl-slug',
            ],
        ]))->setKeyName('slug');

        App::instance(Model::class, $model);

        $this->registerRoute('en/route/{slug}', 'en.route.name');
        $this->registerRoute('nl/route/{slug}', 'nl.route.name');

        $this->assertEquals(url('en/route/en-slug'), route('route.name', [$model]));
        $this->assertEquals(url('en/route/en-slug'), route('route.name', [$model], true, 'en'));
        $this->assertEquals(url('nl/route/nl-slug'), route('route.name', [$model], true, 'nl'));
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
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $callback = function () {
            return Request::hasValidSignature()
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

    /**
     * Register a route.
     *
     * @param string $url
     * @param string $name
     * @param \Closure|null $callback
     *
     * @return \Illuminate\Routing\Route
     */
    protected function registerRoute($url, $name, $callback = null)
    {
        return Route::name($name)->get($url, $callback ?: function () {});
    }
}
