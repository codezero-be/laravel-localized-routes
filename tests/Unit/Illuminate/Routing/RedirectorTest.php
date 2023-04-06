<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit\Illuminate\Routing;

use CodeZero\LocalizedRoutes\Tests\TestCase;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class RedirectorTest extends TestCase
{
    /** @test */
    public function it_redirects_to_a_named_route_in_the_current_locale()
    {
        $this->setAppLocale('en');

        Route::get('en/target/route')->name('en.target.route');
        Route::get('redirect', function () {
            return Redirect::route('target.route');
        });

        $response = $this->get('/redirect');

        $response->assertRedirect('/en/target/route');
    }

    /** @test */
    public function it_redirects_to_a_named_route_in_a_specific_locale()
    {
        $this->setAppLocale('en');

        Route::get('en/target/route')->name('en.target.route');
        Route::get('nl/target/route')->name('nl.target.route');
        Route::get('redirect', function () {
            return Redirect::route('target.route', [], 302, [], 'nl');
        });

        $response = $this->get('/redirect');

        $response->assertRedirect('/nl/target/route');
    }

    /** @test */
    public function it_redirects_to_a_signed_route_in_the_current_locale()
    {
        $this->setAppLocale('en');

        Route::get('en/target/route')->name('en.target.route');
        Route::get('redirect', function () {
            return Redirect::signedRoute('target.route');
        });

        $response = $this->get('/redirect');

        $response->assertRedirect(URL::signedRoute('target.route'));
    }

    /** @test */
    public function it_redirects_to_a_signed_route_in_a_specific_locale()
    {
        $this->setAppLocale('en');

        Route::get('en/target/route')->name('en.target.route');
        Route::get('nl/target/route')->name('nl.target.route');
        Route::get('redirect', function () {
            return Redirect::signedRoute('target.route', [], null, 302, [], 'nl');
        });

        $response = $this->get('/redirect');

        $response->assertRedirect(URL::signedRoute('target.route', [], null, true, 'nl'));
    }

    /** @test */
    public function it_redirects_to_a_temporary_signed_route_in_the_current_locale()
    {
        $this->setAppLocale('en');

        Route::get('en/target/route')->name('en.target.route');
        Route::get('redirect', function () {
            return Redirect::temporarySignedRoute('target.route', now()->addMinutes(30));
        });

        $response = $this->get('/redirect');

        $response->assertRedirect(URL::temporarySignedRoute('target.route', now()->addMinutes(30)));
    }

    /** @test */
    public function it_redirects_to_a_temporary_signed_route_in_a_specific_locale()
    {
        $this->setAppLocale('en');

        Route::get('en/target/route')->name('en.target.route');
        Route::get('nl/target/route')->name('nl.target.route');
        Route::get('redirect', function () {
            return Redirect::temporarySignedRoute('target.route', now()->addMinutes(30), [], 302, [], 'nl');
        });

        $response = $this->get('/redirect');

        $response->assertRedirect(URL::temporarySignedRoute('target.route', now()->addMinutes(30), [], true, 'nl'));
    }
}
