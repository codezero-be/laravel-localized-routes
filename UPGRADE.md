# Upgrade Guide

## Upgrading To 3.0 From v2.x

### Middleware Changes

Applying the `CodeZero\LocalizedRoutes\Middleware\SetLocale` middleware is now more straightforward.

The middleware is no longer automatically applied to localized routes if the `use_locale_middleware` option is set to `true`.
If you choose to use the middleware, you need to apply it manually to your routes.

Behind the scenes, the middleware will always use [codezero/laravel-localizer](https://github.com/codezero-be/laravel-localizer), regardless of the `use_localizer` option.
No further configuration is needed as essential settings are automatically copied over by our middleware.
Pushing all middleware logic to Localizer will positively influence maintainability of this package.

#### Actions required:

- Remove the `use_locale_middleware` option from your published `config/localized-routes.php` config file.
- Remove the `use_localizer` option from your published `config/localized-routes.php` config file.
- Make sure you apply the middleware to your routes manually, either on specific routes or route groups, or by adding it to the `web` middleware group in `app/Http/Kernel.php`.
- Make sure you also add the middleware to the `$middlewarePriority` array in `app/Http/Kernel.php` in the correct spot:

```php
protected $middlewarePriority = [
    \Illuminate\Session\Middleware\StartSession::class, // <= after this
    //...
    \CodeZero\LocalizedRoutes\Middleware\SetLocale::class,
    //...
    \Illuminate\Routing\Middleware\SubstituteBindings::class, // <= before this
];
```
### Supported Locales, slugs and domains

You can now configure your supported locales in 3 formats.

1. A simple array; in this case, the locales will be used as slugs in the URL's.

```php
'supported-locales' => ['en', 'nl'];
```

2. An array with locale / domain pairs, where the locale is used for route names etc., and the domain for the URL.

```php
'supported-locales' => [
    'en' => 'english-domain.test',
    'nl' => 'dutch-domain.test',
];
```

3. An array with locale / slug pairs, where the locale is used for route names etc., and the slug for the URL.

```php
'supported-locales' => [
    'en' => 'english-slug',
    'nl' => 'dutch-slug',
];
```

#### Actions required:

- Remove the `custom_prefixes` option from your published `config/localized-routes.php` config file.
- Make sure you configure the `supported-locales` option properly if you are using custom slugs.
- Slugs can not contain dots, because then it is considered a domain.
