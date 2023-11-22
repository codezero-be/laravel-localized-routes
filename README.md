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
- [Configure](#-configure)
  - [Publish Configuration File](#-publish-configuration-file)
  - [Configure Supported Locales](#-configure-supported-locales)
    - [Simple Locales](#simple-locales)
    - [Custom Slugs](#custom-slugs)
    - [Custom Domains](#custom-domains)
  - [Use Fallback Locale](#-use-fallback-locale)
  - [Omit Slug for Main Locale](#-omit-slug-for-main-locale)
  - [Scoped Options](#-scoped-options)
- [Add Middleware to Update App Locale](#-add-middleware-to-update-app-locale)
  - [Detectors](#detectors)
  - [Stores](#stores)
- [Register Routes](#-register-routes)
  - [Translate Parameters with Route Model Binding](#-translate-parameters-with-route-model-binding)
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
- Composer ^2.3 (for [codezero/composer-preload-files](https://github.com/codezero-be/composer-preload-files))

## ⬆ Upgrade

Upgrading to a new major version?
Check our [upgrade guide](UPGRADE.md) for instructions.

## 📦 Install

Install this package with Composer:

```bash
composer require codezero/laravel-localized-routes
```

Laravel will automatically register the ServiceProvider.

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

## 🧩 Add Middleware to Update App Locale

By default, the app locale will always be what you configured in `config/app.php`.
To automatically update the app locale, you need to register the middleware.

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
If you don't see the `$middlewarePriority` array, you can copy it from the parent class `Illuminate\Foundation\Http\Kernel`.

Make sure to add it after `StartSession` and before `SubstituteBindings` to trigger it in the correct order:

```php
protected $middlewarePriority = [
    \Illuminate\Session\Middleware\StartSession::class, // <= after this
    //...
    \CodeZero\LocalizedRoutes\Middleware\SetLocale::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class, // <= before this
];
```

### Detectors

The middleware runs the following detectors in sequence, until one returns a supported locale:

|  #  | Detector                | Description                                                            |
|:---:|-------------------------|------------------------------------------------------------------------|
| 1.  | `RouteActionDetector`   | Required. The locales of a localized route is saved in a route action. |
| 2.  | `UrlDetector`           | Required. Tries to find a locale based on the URL slugs or domain.     |
| 3.  | `OmittedLocaleDetector` | Required if an omitted locale is configured. This will always be used. |
| 4.  | `UserDetector`          | Checks a configurable `locale` attribute on the authenticated user.    |
| 5.  | `SessionDetector`       | Checks the session for a previously stored locale.                     |
| 6.  | `CookieDetector`        | Checks a cookie for a previously stored locale.                        |
| 7.  | `BrowserDetector`       | Checks the preferred language settings of the visitor's browser.       |
| 8.  | `AppDetector`           | Required. Checks the default app locale as a last resort.              |

Update the `detectors` array in the config file to choose which detectors to run and in what order.

> You can create your own detector by implementing the `CodeZero\LocalizedRoutes\Middleware\Detectors\Detector` interface and add a reference to it in the config file. The detectors are resolved from Laravel's IOC container, so you can add any dependencies to your constructor.

### Stores

If a supported locale is detected, it will automatically be stored in:

|  #  | Store          | Description                                         |
|:---:|----------------|-----------------------------------------------------|
| 1.  | `SessionStore` | Stores the locale in the session.                   |
| 2.  | `CookieStore`  | Stores the locale in a cookie.                      |
| 3.  | `AppStore`     | Required. Sets the locale as the active app locale. |

Update the `stores` array in the config to choose which stores to use.

> You can create your own store by implementing the `CodeZero\LocalizedRoutes\Middleware\Stores\Store` interface and add a reference to it in the config file. The stores are resolved from Laravel's IOC container, so you can add any dependencies to your constructor.

Although no further configuration is needed, you can change advanced settings in the config file.

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

| URI         | Name       |
|-------------|------------|
| `/en/about` | `en.about` |
| `/nl/about` | `nl.about` |

And with the omitted locale set to `en`, the result would be:

| URI         | Name       |
|-------------|------------|
| `/about`    | `en.about` |
| `/nl/about` | `nl.about` |

> In a most practical scenario, you would register a route either localized or non-localized, but not both.
> If you do, you will always need to specify a locale to generate the URL with the `route()` helper, because existing route names always have priority.
> Especially when omitting a main locale from the URL, this would be problematic, because you can't have, for example, a localized `/about` route and a non-localized `/about` route in this case.
> The same idea applies to the `/` (root) route! Also note that the route names still have the locale prefix even if the slug is omitted.

### ☑ Translate Parameters with Route Model Binding

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

Routes with translated URIs need to have a name in order to generate localized versions of it using the `route()` helper or the `Route::localizedUrl()` macro.
Because these routes have different slugs depending on the locale, the route name is the only thing that links them together.

First, you create a `routes.php` translation file in your app's `lang` folder for each locale, for example:

```php
lang/nl/routes.php
lang/fr/routes.php
```

Then you add the appropriate translations to each file:

```php
// lang/nl/routes.php
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

The URI macro accepts 2 additional parameters:

1. A locale, in case you need translations to a locale other than the current app locale.
2. A namespace, in case your translation files reside in a package.

```php
Lang::uri('hello/world', 'fr', 'my-package');
```

You can also use `trans()->uri('hello/world')` instead of `Lang::uri('hello/world')`.

### Example

Using these example translations:

```php
// lang/nl/routes.php
return [
    'hello' => 'hallo',
    'world' => 'wereld',
    'override/hello/world' => 'something/very/different',
    'hello/world/{parameter}' => 'uri/with/{parameter}',
];
```

These are possible translation results:

```php
// Translate every slug individually
// Translates to: 'hallo/wereld'
Lang::uri('hello/world');

// Keep original slug when missing translation
// Translates to: 'hallo/big/wereld'
Lang::uri('hello/big/world');

// Translate slugs, but not parameter placeholders
// Translates to: 'hallo/{world}'
Lang::uri('hello/{world}');

// Translate full URIs if an exact translation exists
// Translates to: 'something/very/different'
Lang::uri('override/hello/world');

// Translate full URIs if an exact translation exists (with placeholder)
// Translates to: 'uri/with/{parameter}'
Lang::uri('hello/world/{parameter}');
```

### 🔦 Localize 404 Pages

A standard `404` response has no actual `Route` and does not go through the middleware.
This means our middleware will not be able to update the locale and the request can not be localized.

To fix this, you can register this fallback route at the end of your `routes/web.php` file:

```php
Route::fallback(\CodeZero\LocalizedRoutes\Controllers\FallbackController::class);
```

Because the fallback route is an actual `Route`, the middleware will run and update the locale.

The fallback route is a "catch all" route that Laravel provides.
If you type in a URL that doesn't exist, this route will be triggered instead of a typical 404 exception.

The `FallbackController` will attempt to respond with a 404 error view, located at `resources/views/errors/404.blade.php`.
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

#### Pass Localized Parameters Manually

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

#### Use a Custom Localized Route Key

You can let Laravel resolve localized parameters automatically by adding the `getRouteKey()` method to your model:

```php
public function getRouteKey()
{
    $locale = App::getLocale();
    
    $slugs = [
        'en' => 'en-slug',
        'nl' => 'nl-slug',
    ];

    return $slugs[$locale] ?? '';
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

#### Pass Parameters Manually

Just like with the `route()` helper, you can pass parameters as a second argument.

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

Now you can pass a localized slug to the macro:

```php
$current = Route::localizedUrl(null, [$post->getSlug()]);
$en = Route::localizedUrl('en', [$post->getSlug('en')]);
$nl = Route::localizedUrl('nl', [$post->getSlug('nl')]);
```

#### Use a Custom Route Key

If you add the model's `getRouteKey()` method, you don't need to pass the parameter at all.

```php
public function getRouteKey()
{
    $locale = App::getLocale();
    
    $slugs = [
        'en' => 'en-slug',
        'nl' => 'nl-slug',
    ];

    return $slugs[$locale] ?? '';
}
```

The macro will now automatically figure out what parameters the current route has and fetch the values.

```php
$current = Route::localizedUrl();
$en = Route::localizedUrl('en');
$nl = Route::localizedUrl('nl');
```

#### Multiple Route Keys

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

#### Keep or Remove Query String

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

Generating a localized signed route and temporary signed route URL is just as easy as generating normal route URLs.
Pass it the route name, the necessary parameters, and you will get the URL for the current locale.

```php
$signedUrl = URL::signedRoute('reset.password', ['user' => $id]);
$signedUrl = URL::temporarySignedRoute('reset.password', now()->addMinutes(30), ['user' => $id]);
```

You can also generate a signed route URL for a specific locale:

```php
$signedUrl = URL::signedRoute('reset.password', ['user' => $id], null, true, 'nl');
$signedUrl = URL::temporarySignedRoute('reset.password', now()->addMinutes(30), ['user' => $id], true, 'nl');
```

Check out the [Laravel docs](https://laravel.com/docs/urls#signed-urls) for more info on signed routes.

## 🚌 Redirect to Routes

You can redirect to routes, just like you would in a normal Laravel app, using the `redirect()` helper or the `Redirect` facade.

If you register an `about` route that is not localized, then `about` is an existing route name and its URL will be redirected to.
Otherwise, this will try to redirect to the `about` route for the active locale, e.g. `en.about`:

```php
return redirect()->route('about');
```

You can also redirect to URLs in a specific locale:

```php
// Redirects to 'nl.about'
return redirect()->route('about', [], 302, [], 'nl');
```

A localized version of the `signedRoute` and `temporarySignedRoute` redirects are included as well:

```php
// Redirects to the active locale
return redirect()->signedRoute('signed.route', ['user' => $id]);
return redirect()->temporarySignedRoute('signed.route', now()->addMinutes(30), ['user' => $id]);

// Redirects to 'nl.signed.route'
return redirect()->signedRoute('signed.route', ['user' => $id], null, 302, [], 'nl');
return redirect()->temporarySignedRoute('signed.route', now()->addMinutes(30), ['user' => $id], 302, [], 'nl');
```

## 🪧 Automatically Redirect to Localized URLs

To redirect any non-localized URL to its localized version, you can set the config option `redirect_to_localized_urls` to `true`, and register the following fallback route with the `FallbackController` at the end of your `routes/web.php` file.

```php
Route::fallback(\CodeZero\LocalizedRoutes\Controllers\FallbackController::class);
```

The fallback route is a "catch all" route that Laravel provides.
If you type in a URL that doesn't exist, this route will be triggered instead of a typical 404 exception.

The `FallbackController` will attempt to redirect to a localized version of the URL, or return a [localized 404 response](#-localize-404-pages) if it doesn't exist.

For example:

| URI      | Redirects To |
|----------|--------------|
| `/`      | `/en`        |
| `/about` | `/en/about`  |

If the omitted locale is set to `en`:

| URI         | Redirects To |
|-------------|--------------|
| `/en`       | `/`          |
| `/en/about` | `/about`     |

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
