# Laravel Localized Routes

This package is in development. Feedback is much appreciated!

## Features

- [Automatically register](#register-routes) a route for each locale you wish to support.
- [Generate localized route URL's](#generate-route-urls) in the simplest way using the `route()` helper.
- [Redirect to localized routes](#redirect-to-routes) using the `redirect()->route()` helper.
- Allow routes to be [cached](#cache-routes).
- Let you work with routes without thinking too much about locales.
- Optionally [translate each segment](#translate-routes) in your URI's.

## Install

```
composer require codezero/laravel-localized-routes
```

> Laravel >= 5.5 will automatically register the ServiceProvider.

## Configure Supported Locales

Add a `locales` key to your `config/app.php` file.

```php
'locales' => [
    'en',
    'nl',
    //...
],
```

## Register Routes

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

In the above example there are 5 routes being registered. The routes defined in the `Route::localized` closure are automatically registered for each configured locale. This will prepend the locale to the route's URI and name.

| URI               | Name                   |
| ----------------- | ---------------------- |
| /home             | home                   |
| /en/about         | en.about               |
| /nl/about         | nl.about               |
| /en/admin/reports | en.admin.reports.index |
| /nl/admin/reports | nl.admin.reports.index |

## Generate Route URL's

You can get the URL of your named routes as usual, using the `route()` helper.

Normally you would have to include the locale whenever you want to generate a URL:

```php
$url = route(app()->getLocale().'.admin.reports.index');
```

Because that's rather ugly, this package overwrites the `route()` function and the underlying `UrlGenerator` class with an additional, optional `$locale` argument and takes care of the locale prefix for you. If you don't specify a locale, either a normal, non-localized route or a route in the current locale is returned.

```php
route($name, $parameters = [], $absolute = true, $locale = null)
```

A few examples:

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

> **Note:** in a most practical scenario you would register a route either localized **or** non-localized, but not both. If you do, you will always need to specify a locale to get the URL, because non-localized routes always have priority when using the `route()` function.

## Redirect to Routes

Laravel's `Redirector` uses the same `UrlGenerator` as the `route()` function behind the scenes. Because we are overriding this class, you can easily redirect to your routes.

```php
return redirect()->route('home'); // redirects to /home
return redirect()->route('about'); // redirects to /en/about (current locale)
```

You can't redirect to URL's in a specific locale this way, but if you need to, you can of course just use the `route()` function.

```php
return redirect(route('about', [], true, 'nl')); // redirects to /nl/about
```

## Translate Routes

If you want to translate the segments of your URI's, create a `routes.php` language file for each locale you [configured](#configure-supported-locales):

```
resources/
 |-lang/
    |-en/
    |  |-routes.php
    |-nl/
       |-routes.php
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

The above will generate:

- /en/about/us
- /nl/over/ons

> If a translation is not found, the original segment is used.

## Route Placeholders

Placeholders are not translated via language files. These are values you would provide via the `route()` function. The `Lang::uri()` macro will skip any placeholder segment.

If you have a model that uses a route key that is translated in the current locale, then you can still simply pass the model to the `route()` function to get translated URL's.

An example...

#### Given we have a model like this:

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

#### If we have a localized route like this:

```php
Route::localized(function () {

    Route::get('posts/{post}', PostsController::class.'@show')
        ->name('posts.show');

});
```

#### We can now get the URL with the appropriate slug:

```php
app()->setLocale('en');
app()->getLocale(); // 'en'

$post = new Post;

$url = route('posts.show', $post); // /en/posts/en-slug
$url = route('posts.show', $post, true, 'nl'); // /nl/posts/nl-slug
```

## Cache Routes

In production you can safely cache your routes per usual.

```php
php artisan route:cache
```

## Testing

```
composer test
```

## Security

If you discover any security related issues, please [e-mail me](mailto:ivan@codezero.be) instead of using the issue tracker.

## Changelog

See a list of important changes in the [changelog](CHANGELOG.md).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
