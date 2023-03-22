# Laravel Localized Routes

[![GitHub release](https://img.shields.io/github/release/codezero-be/laravel-localized-routes.svg?style=flat-square)](https://github.com/codezero-be/laravel-localized-routes/releases)
[![Laravel](https://img.shields.io/badge/laravel-10-red?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![License](https://img.shields.io/packagist/l/codezero/laravel-localized-routes.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/github/actions/workflow/status/codezero-be/laravel-localized-routes/run-tests.yml?style=flat-square&logo=github&logoColor=white&label=tests)](https://github.com/codezero-be/laravel-localized-routes/actions)
[![Code Coverage](https://img.shields.io/codacy/coverage/a5db8a1321664e67900c96eadc575ece/master?style=flat-square)](https://app.codacy.com/gh/codezero-be/laravel-localized-routes)
[![Code Quality](https://img.shields.io/codacy/grade/a5db8a1321664e67900c96eadc575ece/master?style=flat-square)](https://app.codacy.com/gh/codezero-be/laravel-localized-routes)
[![Total Downloads](https://img.shields.io/packagist/dt/codezero/laravel-localized-routes.svg?style=flat-square)](https://packagist.org/packages/codezero/laravel-localized-routes)

[![ko-fi](https://www.ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/R6R3UQ8V)

A convenient way to set up and use localized routes in a Laravel app.

## 📖 Table of Contents

- [Requirements](#-requirements)
- [Upgrade](#-upgrade)
- [Install](#-install)
- [Add Middleware](#-add-middleware)
- [Configure](#-configure)
  - [Publish Configuration File](#-publish-configuration-file)
  - [Configure Supported Locales](#-configure-supported-locales)
    - [Simple Locales](#simple-locales)
    - [Custom Slugs](#custom-slugs)
    - [Custom Domains](#custom-domains)
  - [Use Fallback Locale](#-use-fallback-locale)
  - [Omit Slug for Main Locale](#-omit-slug-for-main-locale)
  - [Scoped Options](#-scoped-options)
- [Register Routes](#-register-routes)
  - [Translate parameters with Route Model Binding](#-translate-parameters-with-route-model-binding)
  - [Translate Hard-Coded URI Slugs](#-translate-hard-coded-uri-slugs)
- [Localize 404 Pages](#-localize-404-pages)
- [Cache Routes](#-cache-routes)
- [Generate Route URLs](#-generate-route-urls)
  - [Generate URLs for the Active Locale](#-generate-urls-for-the-active-locale)
  - [Generate URLs for a Specific Locale](#-generate-urls-for-a-specific-locale)
  - [Generate URLs with Localized Parameters](#-generate-urls-with-localized-parameters)
  - [Fallback URLs](#-fallback-urls)
  - [Generate Localized Versions of the Current URL](#-generate-localized-versions-of-the-current-url)
  - [Example Locale Switcher](#-example-locale-switcher)
- [Generate Signed Route URLs](#-generate-signed-route-urls)
- [Redirect to Routes](#-redirect-to-routes)
- [Automatically Redirect to Localized URLs](#-automatically-redirect-to-localized-urls)
- [Helpers](#-helpers)
- [Testing](#-testing)
- [Credits](#-credits)
- [Security](#-security)
- [Changelog](#-changelog)
- [License](#-license)

## ✅ Requirements

- PHP >= 7.2.5
- Laravel >= 7.0

## ⬆ Upgrade

Upgrading to a new major version?
Check our [upgrade guide](UPGRADE.md) for instructions.

## 📦 Install

Install this package with Composer:

```bash
composer require codezero/laravel-localized-routes
```

Laravel will automatically register the ServiceProvider.

## 🧩 Add Middleware

By default, the app locale will always be what you configured in `config/app.php`.

To automatically detect and update the app locale, you need to use a middleware.

Add the middleware to the `web` middleware group in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        //...
        \CodeZero\LocalizedRoutes\Middleware\SetLocale::class,
    ],
];
```

You also need to add the middleware to the `$middlewarePriority` array in `app/Http/Kernel.php`.

Make sure to add it after `StartSession` and before `SubstituteBindings` to trigger it in the correct order:

```php
protected $middlewarePriority = [
    \Illuminate\Session\Middleware\StartSession::class, // <= after this
    //...
    \CodeZero\LocalizedRoutes\Middleware\SetLocale::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class, // <= before this
];
```

If you don't see the `$middlewarePriority` array in your kernel file,
then you can copy it over from the parent class `Illuminate\Foundation\Http\Kernel`.

Under the hood, this package uses [codezero/laravel-localizer](https://github.com/codezero-be/laravel-localizer).
It will look for a preferred locale in a number of places, including the URL, the session, a cookie and the browser.
Additionally, it will also store the app locale in the session and in a cookie.
You can disable any of these by publishing the Localizer config file:

```bash
php artisan vendor:publish --provider="CodeZero\Localizer\LocalizerServiceProvider" --tag="config"
```

The middleware included in this package will overwrite the essential settings in the Localizer config, so you don't have to keep them in sync.
These settings are `supported_locales`, `omitted_locale`, `route_action` and `trusted_detectors`.
These are required for this package to function properly.

## ⚙ Configure

### ☑ Publish Configuration File

```bash
php artisan vendor:publish --provider="CodeZero\LocalizedRoutes\LocalizedRoutesServiceProvider" --tag="config"
```

You will now find a `localized-routes.php` file in the `config` folder.

### ☑ Configure Supported Locales

#### Simple Locales

Add any locales you wish to support to your published `config/localized-routes.php` file:

```php
'supported_locales' => ['en', 'nl'];
```

These locales will be used as a slug, prepended to the URL of your localized routes.

#### Custom Slugs

You can also use a custom slug for a locale:

```php
'supported_locales' => [
    'en' => 'english-slug',
    'nl' => 'dutch-slug',
];
```

#### Custom Domains

Or you can use a custom domain for a locale:

```php
'supported_locales' => [
    'en' => 'english-domain.test',
    'nl' => 'dutch-domain.test',
];
```

### ☑ Use Fallback Locale

When using the `route()` helper to generate a URL for a locale that is not supported, a `Symfony\Component\Routing\Exception\RouteNotFoundException` is thrown by Laravel.
However, you can configure a fallback locale to attempt to resolve a fallback URL instead.
If that fails too, the exception is thrown.

```php
'fallback_locale' => 'en',
```

### ☑ Omit Slug for Main Locale

Specify your main locale if you want to omit its slug from the URL:

```php
'omitted_locale' => 'en',
```

This option has no effect if you use domains instead of slugs.

### ☑ Scoped Options

To set an option for one localized route group only, you can specify it as the second parameter of the localized route macro.
This will override the config file settings. Currently, only 2 options can be overridden.

```php
Route::localized(function () {
    Route::get('about', [AboutController::class, 'index']);
}, [
    'supported_locales' => ['en', 'nl', 'fr'],
    'omitted_locale' => 'en',
]);
```

## 🚘 Register Routes

Define your routes inside the `Route::localized()` closure, to automatically register them for each locale.

This will prepend the locale to the route's URI and name.
If you configured custom domains, it will use those instead of the URI slugs.
You can also use route groups inside the closure.

```php
Route::localized(function () {
    Route::get('about', [AboutController::class, 'index'])->name('about');
});
```

With supported locales `['en', 'nl']`, the above would register:

- `/en/about` with the name `en.about`
- `/nl/about` with the name `nl.about`

And with the omitted locale set to `en`, the result would be:

- `/about` with the name `en.about`
- `/nl/about` with the name `nl.about`

> In a most practical scenario, you would register a route either localized or non-localized, but not both.
> If you do, you will always need to specify a locale to generate the URL with the `route()` helper, because existing route names always have priority.
> Especially when omitting a main locale from the URL, this would be problematic, because you can't have, for example, a localized `/about` route and a non-localized `/about` route in this case.
> The same idea applies to the `/` (root) route! Also note that the route names still have the locale prefix even if the slug is omitted.

### ☑ Translate parameters with Route Model Binding

When resolving incoming route parameters from a request, you probably rely on [Laravel's route model binding](https://laravel.com/docs/routing#route-model-binding).
You typehint a model in the controller, and it will look for a `{model}` by its ID, or by a specific attribute like `{model:slug}`.
If it finds one that matches the parameter value in the URL, it is injected in the controller.

```php
// Example: use the post slug as the route parameter
Route::get('posts/{post:slug}', [PostsController::class, 'index']);

// PostsController.php
public function index(Post $post)
{
    return $post;
}
```

However, to resolve a localized parameter you need to add a `resolveRouteBinding()` method to your model.
In this method you need to write the logic required to find a match, using the parameter value from the URL.

For example, you might have a JSON column in your database containing translated slugs:

```php
public function resolveRouteBinding($value, $field = null)
{
    // Default field to query if no parameter field is specified
    $field = $field ?: $this->getRouteKeyName();
    
    // If the parameter field is 'slug',
    // lets query a JSON field with translations
    if ($field === 'slug') {
        $field .= '->' . App::getLocale(); 
    }
    
    // Perform the query to find the parameter value in the database
    return $this->where($field, $value)->firstOrFail();
}
```

> If you are looking for a good solution to implement translated attributes on your models, be sure to check out [spatie/laravel-translatable](https://github.com/spatie/laravel-translatable).

### ☑ Translate Hard-Coded URI Slugs

This package includes [codezero/laravel-uri-translator](https://github.com/codezero-be/laravel-uri-translator).
This registers a `Lang::uri()` macro that enables you to translate individual, hard-coded URI slugs.
Route parameters will not be translated by this macro.

First, you create a `routes.php` file in your app's `lang` folder for each locale, for example:

```php
lang/nl/routes.php
```

Then you add your translations to it:

```php
return [
    'about' => 'over',
    'us' => 'ons',
];
```

And finally, you use the macro when registering routes:

```php
Route::localized(function () {
    Route::get(Lang::uri('about/us'), [AboutController::class, 'index'])->name('about');
});
```

Routes with translated URIs need to have a name in order to generate localized versions of it using the `route()` helper or the `Route::localizedUrl()` macro.
Because these routes have different slugs depending on the locale, the route name is the only thing that links them together.

Refer to [codezero/laravel-uri-translator](https://github.com/codezero-be/laravel-uri-translator) to learn how to use the `Lang::uri()` macro in more detail.

### 🔦 Localize 404 Pages

A standard `404` response has no actual `Route` and does not go through the middleware.
Thus, it can not be localized by default.

However, to localize a `404` page, you need to register the fallback route with the `FallbackController` at the end of your `routes/web.php` file:

```php
Route::fallback(\CodeZero\LocalizedRoutes\Controllers\FallbackController::class);
```

Because the fallback route is an actual `Route`, it will pass through the middleware, thus it can be localized.

The controller will attempt to respond with a 404 error view, located at `resources/views/errors/404.blade.php`.
If this view does not exist, the normal `Symfony\Component\HttpKernel\Exception\NotFoundHttpException` will be thrown.
You can configure which view to use by changing the `404_view` entry in the config file.

Fallback routes will not apply when:

- your existing routes throw a `404` exception (as in `abort(404)`)
- your existing routes throw a `ModelNotFoundException` (like with route model binding)
- your existing routes throw any other exception

## 🗄 Cache Routes

In production, you can safely cache your routes per usual.

```bash
php artisan route:cache
```

## ⚓ Generate Route URLs

### ☑ Generate URLs for the Active Locale

You can get the URL of your named routes as usual, using the `route()` helper.

```php
$url = route('about'); 
```

If you registered an `about` route that is not localized, then `about` is an existing route name and its URL will be returned.
Otherwise, this will try to generate the `about` URL for the active locale, e.g. `en.about`.

### ☑ Generate URLs for a Specific Locale

In some cases, you might need to generate a URL for a specific locale.
For this purpose, an additional locale parameter was added to Laravel's `route()` helper.

```php
$url = route('about', [], true, 'nl'); // this will load 'nl.about'
```

### ☑ Generate URLs with Localized Parameters

There are a number of ways to generate route URLs with localized parameters.

First of all, you can pass the value manually.

Let's say we have a `Post` model with a `getSlug()` method:

```php
public function getSlug($locale = null)
{
    $locale = $locale ?: App::getLocale();
    
    $slugs = [
        'en' => 'en-slug',
        'nl' => 'nl-slug',
    ];

    return $slugs[$locale] ?? '';
}
```

> Of course, in a real project the slugs wouldn't be hard-coded.
> If you are looking for a good solution to implement translated attributes on your models, be sure to check out [spatie/laravel-translatable](https://github.com/spatie/laravel-translatable).

Now you can pass a localized slug to the `route()` function:

```php
route('posts.show', [$post->getSlug()]);
route('posts.show', [$post->getSlug('nl')], true, 'nl');
```

But you can automate this further, by adding one more method to your model:

```php
public function getRouteKey()
{
    return $this->getSlug();
}
```

Now you can just pass the model:

```php
route('posts.show', [$post]);
route('posts.show', [$post], true, 'nl');
```

### ☑ Fallback URLs

A fallback locale can be provided in the config file.
If the locale parameter for the `route()` helper is not a supported locale, the fallback locale will be used instead.

```php
// When the fallback locale is set to 'en'
// and the supported locales are 'en' and 'nl'

$url = route('about', [], true, 'nl'); // this will load 'nl.about'
$url = route('about', [], true, 'wk'); // this will load 'en.about'
```

If neither a regular nor a localized route can be resolved, a `Symfony\Component\Routing\Exception\RouteNotFoundException` will be thrown.

### ☑ Generate Localized Versions of the Current URL

To generate a URL for the current route in any locale, you can use the `Route::localizedUrl()` macro.

Just like with the `route()` helper, you can pass parameters as a second argument.

```php
$current = Route::localizedUrl(null, [$post->getSlug()]);
$en = Route::localizedUrl('en', [$post->getSlug('en')]);
$nl = Route::localizedUrl('nl', [$post->getSlug('nl')]);
```

However, if you add the model's `getRouteKey()` method, you don't need to pass the parameter at all.
The macro will figure it out automatically.

```php
$current = Route::localizedUrl();
$en = Route::localizedUrl('en');
$nl = Route::localizedUrl('nl');
```

If you have a route with multiple keys, like `/en/posts/{id}/{slug}`, then you can implement the `ProvidesRouteParameters` interface in your model.
From the `getRouteParameters()` method, you then return the required parameter values.

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

Now, the parameters will still be resolved automatically:

```php
$current = Route::localizedUrl();
$en = Route::localizedUrl('en');
$nl = Route::localizedUrl('nl');
```

By default, the query string will be included in the generated URL.
If you don't want this, you can pass an extra parameter to the macro:

```php
$keepQuery = false;
$current = Route::localizedUrl(null, [], true, $keepQuery);
```

### ☑ Example Locale Switcher

The following Blade snippet will add a link to the current page in every alternative locale.

It will only run if the current route is localized or a fallback route.

```blade
@if (Route::isLocalized() || Route::isFallback())
    <ul>
        @foreach(LocaleConfig::getLocales() as $locale)
            @if ( ! App::isLocale($locale))
                <li>
                    <a href="{{ Route::localizedUrl($locale) }}">
                        {{ strtoupper($locale) }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
@endif
```

## 🖋 Generate Signed Route URLs

Generating a localized signed route URL is just as easy as generating normal route URLs.
Pass it the route name, the necessary parameters, and you will get the URL for the current locale.

```php
$signedUrl = URL::signedRoute('reset.password', ['user' => $id], now()->addMinutes(30));
```

You can also generate a signed route URL for a specific locale:

```php
$signedUrl = URL::signedRoute('reset.password', ['user' => $id], now()->addMinutes(30), true, 'nl');
```

Check out the [Laravel docs](https://laravel.com/docs/urls#signed-urls) for more info on signed routes.

## 🚌 Redirect to Routes

You can redirect to routes, just like you would in a normal Laravel app.

If you register an `about` route that is not localized, then `about` is an existing route name and its URL will be redirected to.
Otherwise, this will try to generate the `about` URL for the active locale, e.g. `en.about` and redirect there.

```php
return redirect()->route('about');
```

You can't redirect to URL's in a specific locale this way, but if you need to, you can of course just use the `route()` function.

```php
return redirect(route('about', [], true, 'nl')); // this redirects to nl.about
```

## 🪧 Automatically Redirect to Localized URLs

To redirect any non-localized URL to its localized version, you can set the config option `redirect_to_localized_urls` to `true`, and register the following fallback route with the `FallbackController` at the end of your `routes/web.php` file.

```php
Route::fallback(\CodeZero\LocalizedRoutes\Controllers\FallbackController::class);
```

For example:

- `/` would redirect to `/en`
- `/about` would redirect to `/en/about`

If the omitted locale is set to `en`:

- `/en` would redirect to `/`
- `/en/about` would redirect to `/about`

If a route doesn't exist, a `404` response will be returned.

## 🪜 Helpers

### `Route::hasLocalized()`

```php
// Check if a named route exists in the active locale:
$exists = Route::hasLocalized('about');
// Check if a named route exists in a specific locale:
$exists = Route::hasLocalized('about', 'nl');
```

### `Route::isLocalized()`

```php
// Check if the current route is localized:
$isLocalized = Route::isLocalized();
// Check if the current route is localized and has a specific name:
$isLocalized = Route::isLocalized('about');
// Check if the current route has a specific locale and has a specific name:
$isLocalized = Route::isLocalized('about', 'nl');
// Check if the current route is localized and its name matches a pattern:
$isLocalized = Route::isLocalized(['admin.*', 'dashboard.*']);
// Check if the current route has one of the specified locales and has a specific name:
$isLocalized = Route::isLocalized('about', ['en', 'nl']);
```

### `Route::isFallback()`

```php
// Check if the current route is a fallback route:
$isFallback = Route::isFallback();
```

## 🚧 Testing

```bash
composer test
```

## ☕ Credits

- [Ivan Vermeyen](https://github.com/ivanvermeyen)
- [All contributors](https://github.com/codezero-be/laravel-localized-routes/contributors)

## 🔒 Security

If you discover any security related issues, please [e-mail me](mailto:ivan@codezero.be) instead of using the issue tracker.

## 📑 Changelog

A complete list of all notable changes to this package can be found on the
[releases page](https://github.com/codezero-be/laravel-localized-routes/releases).

## 📜 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
