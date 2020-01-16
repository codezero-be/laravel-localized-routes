# Laravel Localized Routes

[![GitHub release](https://img.shields.io/github/release/codezero-be/laravel-localized-routes.svg)]()
[![License](https://img.shields.io/packagist/l/codezero/laravel-localized-routes.svg)]()
[![Build Status](https://scrutinizer-ci.com/g/codezero-be/laravel-localized-routes/badges/build.png?b=master)](https://scrutinizer-ci.com/g/codezero-be/laravel-localized-routes/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/codezero-be/laravel-localized-routes/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/codezero-be/laravel-localized-routes/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/codezero-be/laravel-localized-routes/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/codezero-be/laravel-localized-routes/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/codezero/laravel-localized-routes.svg)](https://packagist.org/packages/codezero/laravel-localized-routes)

[![ko-fi](https://www.ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/R6R3UQ8V)

#### A convenient way to set up and use localized routes in a Laravel app.

## 🧩 Features

- [Automatically register](#-register-routes) a route for each locale.
- Use [URL slugs or custom domains](#%EF%B8%8F-supported-locales) (or subdomains).
- Optionally [omit the locale slug from the URL for your main locale](#%EF%B8%8F-omit-slug-for-main-locale).
- Optionally [translate each segment](#-translate-routes) in your URI's.
- [Generate localized route URL's](#-generate-route-urls) using the `route()` helper.
- [Redirect to localized routes](#-redirect-to-routes) using the `redirect()->route()` helper.
- [Generate localized signed route URL's](#-generate-signed-route-urls).
- [Generate localized versions of the current URL](#%EF%B8%8F-generate-localized-versions-of-the-current-url) with our `Route::localizedUrl()` macro.
- [Automatically set the appropriate app locale](#%EF%B8%8F-use-middleware-to-update-app-locale) using the middleware.
- Optionally [detect and set the preferred locale in multiple sources](#%EF%B8%8F-use-localizer-to-detect-and-set-the-locale).
- Use localized route keys with [route model binding](#-route-model-binding).
- Allow routes to be [cached](#-cache-routes).
- Localize [`404` pages](#-localized-404-pages).

## 🔌 Demo App

To test and demonstrate some of the features this package provides, I have created a basic Laravel 6 demo app.
Feel free to open any issues there if you want me to test/demonstrate more stuff.

- https://github.com/ivanvermeyen/test-app-laravel-localized-routes

## ✅ Requirements

- PHP >= 7.1
- Laravel >= 5.6

## 📦 Install

```bash
composer require codezero/laravel-localized-routes
```

> Laravel will automatically register the ServiceProvider.

## ⚙️ Configure

#### ☑️ Publish Configuration File

```bash
php artisan vendor:publish --provider="CodeZero\LocalizedRoutes\LocalizedRoutesServiceProvider" --tag="config"
```

You will now find a `localized-routes.php` file in the `config` folder.

#### ☑️ Supported Locales

##### Using Slugs

Add any locales you wish to support to your published `config/localized-routes.php` file:

```php
'supported-locales' => ['en', 'nl', 'fr'],
```

 This will automatically prepend a slug to your localized routes. [More on this below](#-register-routes).

##### Using Domains

Alternatively, you can use a different domain or subdomain for each locale by configuring the `supported-locales` like this:

```php
'supported-locales' => [
  'en' => 'example.com',
  'nl' => 'nl.example.com',
  'fr' => 'fr.example.com',
],
```

#### ☑️ Omit Slug for Main Locale

Specify your main locale if you want to omit its slug from the URL:

```php
'omit_url_prefix_for_locale' => null
```

Setting this option to `'en'` will result, for example, in URL's like this:

- English: `/some-url` instead of the default `/en/some-url`
- Dutch: `/nl/some-url` as usual
- French: `/fr/some-url` as usual

> This option has no effect if you use domains instead of slugs.

#### ☑️ Use Middleware to Update App Locale

By default, the app locale will always be what you configured in `config/app.php`.
To automatically update the app locale when a localized route is accessed, you need to use a middleware.

**⚠️ Important note for Laravel 6+**

To make route model binding work in Laravel 6+ you always also need to add the middleware
to the `$middlewarePriority` array in `app/Http/Kernel.php` so it runs before `SubstituteBindings`:

```php
protected $middlewarePriority = [
    \Illuminate\Session\Middleware\StartSession::class, // <= after this
    //...
    \CodeZero\LocalizedRoutes\Middleware\SetLocale::class,
    //...
    \Illuminate\Routing\Middleware\SubstituteBindings::class, // <= before this
];
```

You can then enable the middleware in a few ways:

**For every localized route, via our config file**

Simply set the option to `true` to add the middleware to every localized route:

```php
'use_locale_middleware' => true
```

> This will not add the middleware to non-localized routes!

**OR, for every route using the `web` middleware group**

You can manually add the middleware to the `$middlewareGroups` array in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        \Illuminate\Session\Middleware\StartSession::class, // <= after this
        //...
        \CodeZero\LocalizedRoutes\Middleware\SetLocale::class,
        //...
        \Illuminate\Routing\Middleware\SubstituteBindings::class, // <= before this
    ],
];
```

**OR, for specific routes**

Alternatively, you can add the middleware to a specific route or route group:

```php
Route::localized(function () {

    Route::get('about', AboutController::class.'@index')
        ->name('about')
        ->middleware(\CodeZero\LocalizedRoutes\Middleware\SetLocale::class);

    Route::group([
        'as' => 'admin.',
        'middleware' => [\CodeZero\LocalizedRoutes\Middleware\SetLocale::class],
    ], function () {

        Route::get('admin/reports', ReportsController::class.'@index')
            ->name('reports.index');

    });

});
```

#### ☑️ Use Localizer to Detect and Set the Locale

This package can use [codezero/laravel-localizer](https://github.com/codezero-be/laravel-localizer) to automatically detect and set the locale.

With this option disabled, the app locale will only be updated when accessing localized routes.

With this option enabled, the app locale can also be updated when accessing non-localized routes.
For non-localized routes it will look for a preferred locale in the session, in a cookie or in the browser.
Additionally, it will also store the app locale in the session and in a cookie.

> Enabling this option can be handy if you have, for example, a generic homepage and you want to know the preferred locale.

To enable this option, set it to `true` in the published config file.

```php
'use_localizer' => true
```

This option only has effect on routes that use our `SetLocale` middleware.

> You can review [codezero/laravel-localizer](https://github.com/codezero-be/laravel-localizer),
> publish its config file and tweak it as needed.
> The only option we will override is  `supported-locales`, to match the option in our own config file.

#### ☑️ Set Options for the Current Localized Route Group

To set an option for one localized route group only, you can specify it as the second parameter of the localized route macro.
This will override the config file settings.

```php
Route::localized(function () {

    Route::get('about', AboutController::class.'@index')
        ->name('about');

}, [
    'supported-locales' => ['en', 'nl', 'fr'],
    'omit_url_prefix_for_locale' => null,
    'use_locale_middleware' => false,
]);
```

## 🚗 Register Routes

Example:

```php
// Not localized
Route::get('home', HomeController::class.'@index')
    ->name('home');

// Localized
Route::localized(function () {

    Route::get('about', AboutController::class.'@index')
        ->name('about');

    Route::name('admin.')->group(function () {
        Route::get('admin/reports', ReportsController::class.'@index')
            ->name('reports.index');
    });

});
```

In the above example there are 5 routes being registered.
The routes defined in the `Route::localized` closure are automatically registered for each configured locale.
This will prepend the locale to the route's URI and name. If you configured custom domains, it will use those instead of the slugs.

| URI               | Name                   |
| ----------------- | ---------------------- |
| /home             | home                   |
| /en/about         | en.about               |
| /nl/about         | nl.about               |
| /en/admin/reports | en.admin.reports.index |
| /nl/admin/reports | nl.admin.reports.index |

If you set `omit_url_prefix_for_locale` to `'en'` in the configuration file, the resulting routes look like this: 

| URI               | Name                   |
| ----------------- | ---------------------- |
| /home             | home                   |
| /about            | en.about               |
| /nl/about         | nl.about               |
| /admin/reports    | en.admin.reports.index |
| /nl/admin/reports | nl.admin.reports.index |

**⚠️ Beware that you don't register the same URL twice when omitting the locale.**
You can't have a localized `/about` route and also register a non-localized `/about` route in this case.
The same idea applies to the `/` (root) route! Also note that the route names still have the locale prefix.

### 🔦 Localized `404` Pages

By default, Laravel's `404` pages don't go trough the middleware and have no `Route::current()` associated with it.
Not even when you create your custom `errors.404` view.
Therefor, the locale can't be set to match the requested URL automatically via middleware.

To enable localized `404` pages, you need to register a `fallback` route
and make sure it has the `SetLocale` middleware.
This is basically a catch all route that will trigger for all non existing URL's.

```php
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
})->middleware(\CodeZero\LocalizedRoutes\Middleware\SetLocale::class);
```

Another thing to keep in mind is that a `fallback` route returns a `200` status by default.
So to make it a real `404` you need to return a `404` response yourself.

Fallback routes will not be triggered when:

- your existing routes throw a `404` error (as in `abort(404)`)
- your existing routes throw a `ModelNotFoundException` (like with route model binding)
- your existing routes throw any other exception

Because those routes are in fact registered, the `404` page will have the correct `App::getLocale()` set.

[Here is a good read about fallback routes](https://themsaid.com/laravel-55-better-404-response-20170921).

### 🚕 Generate Route URL's

You can get the URL of your named routes as usual, using the `route()` helper.

##### 👎 The ugly way...

Normally you would have to include the locale whenever you want to generate a URL:

```php
$url = route(app()->getLocale().'.admin.reports.index');
```

##### 👍 A much nicer way...

Because the former is rather ugly, this package overwrites the `route()` function
and the underlying `UrlGenerator` class with an additional, optional `$locale` argument
and takes care of the locale prefix for you. If you don't specify a locale, either a normal,
non-localized route or a route in the current locale is returned.

```php
$url = route('admin.reports.index'); // current locale
$url = route('admin.reports.index', [], true, 'nl'); // dutch URL
```

This is the new route helper signature:

```php
route($name, $parameters = [], $absolute = true, $locale = null)
```

A few examples (given the example routes we registered above):

```php
app()->setLocale('en');
app()->getLocale(); // 'en'

$url = route('home'); // /home (normal routes have priority)
$url = route('about'); // /en/about (current locale)

// Get specific locales...
// This is most useful if you want to generate a URL to switch language.
$url = route('about', [], true, 'en'); // /en/about
$url = route('about', [], true, 'nl'); // /nl/about

// You could also do this, but it kinda defeats the purpose...
$url = route('en.about'); // /en/about
$url = route('en.about', [], true, 'nl'); // /nl/about
```

> **Note:** in a most practical scenario you would register a route either localized **or** non-localized, but not both.
> If you do, you will always need to specify a locale to get the URL, because non-localized routes always have priority
> when using the `route()` function.

### 🚌 Redirect to Routes

Laravel's `Redirector` uses the same `UrlGenerator` as the `route()` function behind the scenes.
Because we are overriding this class, you can easily redirect to your routes.

```php
return redirect()->route('home'); // non-localized route, redirects to /home
return redirect()->route('about'); // localized route, redirects to /en/about (current locale)
```

You can't redirect to URL's in a specific locale this way, but if you need to, you can of course just use the `route()` function.

```php
return redirect(route('about', [], true, 'nl')); // localized route, redirects to /nl/about
```

### ⚓️ Generate Localized Versions of the Current URL

To generate a URL for the current route in any locale, you can use this `Route` macro:

##### With Route Model Binding

If your route uses a localized key (like a slug) and you are using [route model binding](#-route-model-binding),
then the key will automatically be localized.

```php
$current = \Route::localizedUrl(); // /en/posts/en-slug
$en = \Route::localizedUrl('en'); // /en/posts/en-slug
$nl = \Route::localizedUrl('nl'); // /nl/posts/nl-slug
```

If you have a route **with multiple keys**, like `/en/posts/{id}/{slug}`, then you can pass the parameters yourself
(like in the example without route model binding below) or you can implement this interface in your model:

```php
use CodeZero\LocalizedRoutes\ProvidesRouteParameters;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements ProvidesRouteParameters
{
    public function getRouteParameters($locale = null)
    {
        return [
            $this->id,
            $this->getSlug($locale) // Add this method yourself of course :)
        ];
    }
}
```

Now, as long as you use route model binding, you can still just do:

```php
$current = \Route::localizedUrl(); // /en/posts/en-slug
$en = \Route::localizedUrl('en'); // /en/posts/en-slug
$nl = \Route::localizedUrl('nl'); // /nl/posts/nl-slug
```

##### Without Route Model Binding

If you don't use [route model binding](#-route-model-binding) and you need a localized slug in the URL,
then you will have to pass it manually.

For example:

```php
$nl = \Route::localizedUrl('nl'); // Wrong: /nl/posts/en-slug
$nl = \Route::localizedUrl('nl', [$post->getSlug('nl')]); // Right: /nl/posts/nl-slug
```

The `getSlug()` method is just for illustration, so you will need to implement that yourself of course.

### ✍🏻 Generate Signed Route URL's

Generating a [signed route URL](https://laravel.com/docs/urls#signed-urls) is just as easy.

Pass it the route name, the necessary parameters and you will get the URL for the current locale.

```php
$signedUrl = URL::signedRoute('reset.password', ['user' => $id], now()->addMinutes(30));
```

You can also generate a signed URL for a specific locale:

```php
$signedUrl = URL::signedRoute($name, $parameters, $expiration, true, 'nl');
```

Check out the [Laravel docs](https://laravel.com/docs/urls#signed-urls) for more info on signed routes.

### 🌎 Translate Routes

If you want to translate the segments of your URI's, create a `routes.php` language file for each locale you [configured](#configure-supported-locales):

```
resources
 └── lang
      ├── en
      │    └── routes.php
      └── nl
           └── routes.php
```

In these files, add a translation for each segment.

```php
// lang/nl/routes.php
return [
    'about' => 'over',
    'us' => 'ons',
];
```

Now you can use our `Lang::uri()` macro during route registration:

```php
Route::localized(function () {

    Route::get(Lang::uri('about/us'), AboutController::class.'@index')
        ->name('about.us');

});
```

Note that in order to find a translated version of a route, you will need to give your routes a name.
If you don't name your routes, only the parameters (model route keys) will be translated, not the "hard-coded" slugs.

The above will generate:

- /en/about/us
- /nl/over/ons

> If a translation is not found, the original segment is used.

## 🚏 Route Parameters

Parameter placeholders are not translated via language files. These are values you would provide via the `route()` function.
The `Lang::uri()` macro will skip any parameter placeholder segment.

If you have a model that uses a route key that is translated in the current locale,
then you can still simply pass the model to the `route()` function to get translated URL's.

An example...

**Given we have a model like this:**

```php
class Post extends \Illuminate\Database\Eloquent\Model
{
    public function getRouteKey()
    {
        $slugs = [
            'en' => 'en-slug',
            'nl' => 'nl-slug',
        ];

        return $slugs[app()->getLocale()];
    }
}
```

> **TIP:** checkout [spatie/laravel-translatable](https://github.com/spatie/laravel-translatable) for translatable models.

**If we have a localized route like this:**

```php
Route::localized(function () {

    Route::get('posts/{post}', PostsController::class.'@show')
        ->name('posts.show');

});
```

**We can now get the URL with the appropriate slug:**

```php
app()->setLocale('en');
app()->getLocale(); // 'en'

$post = new Post;

$url = route('posts.show', [$post]); // /en/posts/en-slug
$url = route('posts.show', [$post], true, 'nl'); // /nl/posts/nl-slug
```

## 🚴‍ Route Model Binding

If you enable the [middleware](#-use-middleware-to-update-app-locale) included in this package,
you can use [Laravel's route model binding](https://laravel.com/docs/routing#route-model-binding)
to automatically inject models with localized route keys in your controllers.

All you need to do is add a `resolveRouteBinding()` method to your model.
Check [Laravel's documentation](https://laravel.com/docs/routing#route-model-binding)
for alternative ways to enable route model binding.

```php
public function resolveRouteBinding($value)
{
    // Perform the logic to find the given slug in the database...
    return $this->where('slug->'.app()->getLocale(), $value)->firstOrFail();
}
```

> **TIP:** checkout [spatie/laravel-translatable](https://github.com/spatie/laravel-translatable) for translatable models.

## 🗃 Cache Routes

In production you can safely cache your routes per usual.

```bash
php artisan route:cache
```

## 🚧 Testing

```bash
composer test
```

## ☕️ Credits

- [Ivan Vermeyen](https://byterider.io)
- [All contributors](../../contributors)

## 🔓 Security

If you discover any security related issues, please [e-mail me](mailto:ivan@codezero.be) instead of using the issue tracker.

## 📑 Changelog

See a list of important changes in the [changelog](CHANGELOG.md).

## 📜 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
