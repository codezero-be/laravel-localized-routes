<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit\Middleware;

use CodeZero\LocalizedRoutes\Middleware\SetLocale;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class SetLocaleTest extends TestCase
{
    /** @test */
    public function it_looks_for_a_locale_in_a_custom_route_action()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $routeAction = ['locale' => 'nl'];

        Route::group($routeAction, function () {
            Route::get('some/route', function () {
                return App::getLocale();
            })->middleware(['web', SetLocale::class]);
        });

        $response = $this->get('some/route');

        $response->assertSessionHas($this->sessionKey, 'nl');
        $response->assertCookie($this->cookieName, 'nl');
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_looks_for_a_locale_in_the_url()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        Route::get('nl/some/route', function () {
            return App::getLocale();
        })->middleware(['web', SetLocale::class]);

        $response = $this->get('nl/some/route');

        $response->assertSessionHas($this->sessionKey, 'nl');
        $response->assertCookie($this->cookieName, 'nl');
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_looks_for_custom_slugs()
    {
        $this->setSupportedLocales([
            'en' => 'english',
            'nl' => 'dutch',
        ]);
        $this->setAppLocale('en');

        Route::get('dutch/some/route', function () {
            return App::getLocale();
        })->middleware(['web', SetLocale::class]);

        $response = $this->get('dutch/some/route');

        $response->assertSessionHas($this->sessionKey, 'nl');
        $response->assertCookie($this->cookieName, 'nl');
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_looks_for_custom_domains()
    {
        $this->setSupportedLocales([
            'en' => 'english.test',
            'nl' => 'dutch.test',
        ]);
        $this->setAppLocale('en');

        Route::group(['domain' => 'dutch.test'], function () {
            Route::get('some/route', function () {
                return App::getLocale();
            })->middleware(['web', SetLocale::class]);
        });

        $response = $this->get('http://dutch.test/some/route');

        $response->assertSessionHas($this->sessionKey, 'nl');
        $response->assertCookie($this->cookieName, 'nl');
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_checks_for_a_configured_omitted_locale()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $this->setOmittedLocale('nl');

        Route::get('some/route', function () {
            return App::getLocale();
        })->middleware(['web', SetLocale::class]);

        $response = $this->get('some/route');

        $response->assertSessionHas($this->sessionKey, 'nl');
        $response->assertCookie($this->cookieName, 'nl');
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_looks_for_a_locale_on_the_authenticated_user()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $attribute = Config::get('localized-routes.user_attribute');
        $user = new User();
        $user->$attribute = 'nl';

        Route::get('some/route', function () {
            return App::getLocale();
        })->middleware(['web', SetLocale::class]);

        $response = $this->actingAs($user)->get('some/route');

        $response->assertSessionHas($this->sessionKey, 'nl');
        $response->assertCookie($this->cookieName, 'nl');
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_will_bypass_missing_attribute_exception_if_the_locale_attribute_is_missing_on_the_user_model()
    {
        if (version_compare(App::version(), '9.35.0') === -1) {
            $this->markTestSkipped('This test only applies to Laravel 9.35.0 and higher.');
        }

        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $user = new User();
        $user->exists = true; // exception is only thrown if user "exists"
        Model::preventAccessingMissingAttributes();

        Route::get('some/route', function () {
            return App::getLocale();
        })->middleware(['web', SetLocale::class]);

        $response = $this->actingAs($user)->get('some/route');

        $response->assertSessionHas($this->sessionKey, 'en');
        $response->assertCookie($this->cookieName, 'en');
        $this->assertEquals('en', $response->original);
    }

    /** @test */
    public function it_looks_for_a_locale_in_the_session()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $this->setSessionLocale('nl');

        Route::get('some/route', function () {
            return App::getLocale();
        })->middleware(['web', SetLocale::class]);

        $response = $this->get('some/route');

        $response->assertSessionHas($this->sessionKey, 'nl');
        $response->assertCookie($this->cookieName, 'nl');
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_looks_for_a_locale_in_a_cookie()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $cookie = 'nl';

        Route::get('some/route', function () {
            return App::getLocale();
        })->middleware(['web', SetLocale::class]);

        $response = $this->withCookie($this->cookieName, $cookie)
            ->get('some/route');

        $response->assertSessionHas($this->sessionKey, 'nl');
        $response->assertCookie($this->cookieName, 'nl');
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_looks_for_a_locale_in_the_browser()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('en');

        $this->setBrowserLocales('nl');

        Route::get('some/route', function () {
            return App::getLocale();
        })->middleware(['web', SetLocale::class]);

        $response = $this->get('some/route');

        $response->assertSessionHas($this->sessionKey, 'nl');
        $response->assertCookie($this->cookieName, 'nl');
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_returns_the_best_match_when_a_browser_locale_is_used()
    {
        $this->setSupportedLocales(['en', 'nl', 'fr']);
        $this->setAppLocale('en');

        $this->setBrowserLocales('de,fr;q=0.4,nl-BE;q=0.8');

        Route::get('some/route', function () {
            return App::getLocale();
        })->middleware(['web', SetLocale::class]);

        $response = $this->get('some/route');

        $response->assertSessionHas($this->sessionKey, 'nl');
        $response->assertCookie($this->cookieName, 'nl');
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_looks_for_the_current_app_locale()
    {
        $this->setSupportedLocales(['en', 'nl']);
        $this->setAppLocale('nl');

        Route::get('some/route', function () {
            return App::getLocale();
        })->middleware(['web', SetLocale::class]);

        $response = $this->get('some/route');

        $response->assertSessionHas($this->sessionKey, 'nl');
        $response->assertCookie($this->cookieName, 'nl');
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function trusted_detectors_ignore_supported_locales_and_may_set_any_locale()
    {
        $this->setSupportedLocales(['en']);
        $this->setAppLocale('en');

        $routeAction = ['locale' => 'nl'];

        Config::set('localized-routes.trusted_detectors', [
            \CodeZero\LocalizedRoutes\Middleware\Detectors\RouteActionDetector::class,
        ]);

        Route::group($routeAction, function () {
            Route::get('some/route', function () {
                return App::getLocale();
            })->middleware(['web', SetLocale::class]);
        });

        $response = $this->get('some/route');

        $response->assertSessionHas($this->sessionKey, 'nl');
        $response->assertCookie($this->cookieName, 'nl');
        $this->assertEquals('nl', $response->original);
    }

    /** @test */
    public function it_sets_the_locale_of_routes_with_scoped_config()
    {
        $this->setSupportedLocales(['en']);
        $this->setAppLocale('en');
        $this->setOmittedLocale(null);

        Route::localized(function () {
            Route::get('with-scoped-config', function () {
                return App::getLocale();
            })->middleware(['web', SetLocale::class]);
        }, [
            'omitted_locale' => 'en',
            'supported_locales' => ['en', 'nl', 'de'],
        ]);

        $response = $this->get('with-scoped-config');
        $this->assertEquals('en', $response->original);

        $response = $this->get('nl/with-scoped-config');
        $this->assertEquals('nl', $response->original);

        $response = $this->get('de/with-scoped-config');
        $this->assertEquals('de', $response->original);
    }
}
