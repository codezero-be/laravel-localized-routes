# Changelog

All Notable changes to **Laravel Localized Routes** will be documented in this file.

## 2.2.6 (2020-03-03)

- Add support for Laravel 7

## 2.2.5 (2020-03-03)

- Constrain dependency versions

## 2.2.4 (2020-01-17)

- Accept query string parameters via `Route::localizedUrl()`
- Handle optional route parameters with `Route::localizedUrl()`
- Handle capitalized route parameters with `Route::localizedUrl()`

## 2.2.3 (2020-01-16)

- Return a URL with query string from `Route::localizedUrl()`
- Use the `route()` helper for named routes to support translated slugs with the `Lang::uri()` macro
- Improve unregistered route handling in the `UrlGenerator`

## 2.2.2 (2020-01-09)

- Generate absolute and non absolute URL's using `Route::localizedUrl($locale, $parameters, $absolute)`
- Fallback routes no longer require a name of `404`
- Refactor code and improve tests

## 2.2.1 (2020-01-08)

- Fix issue where first slug was duplicated on fallback routes

## 2.2.0 (2020-01-08)

- Localize 404 and fallback URL's
- Add README instructions on how to localize your 404 pages properly
- Add `Route::isLocalized()` macro
- Fix issue with `Route::localizedUrl()` on 404 pages (#16)
- Fix issue with `Route::localizedUrl()` on non localized routes
- Fix issue with generating localized URL's using custom domains
- Enable use of Route::localizedUrl() with unnamed routes

## 2.1.0 (2019-12-22)

- Add `Route::localizedUrl()` macro to generate a URL for the current route in any locale.

## 2.0.0 (2019-12-17)

- Add option to automatically detect and set locales with [codezero/laravel-localizer](https://github.com/codezero-be/laravel-localizer)
- Rename middleware to `SetLocale`

## 1.3.4 (2019-12-10)

- Refactor tests
- Update README

## 1.3.3 (2019-09-05)

- Add support for Laravel 6

## 1.3.2 (2019-08-31)

- Add locale middleware for localized routes

## 1.3.1 (2019-06-11)

- Swap `funkjedi/composer-include-files` with `0.0.0/composer-include-files`.
 The former was no longer working in vendor packages (https://github.com/funkjedi/composer-include-files/pull/9).

## 1.3.0 (2019-05-14)

- Add support for signed Routes with locale (#5)

## 1.2.0 (2019-04-30)

- Enable the use of customs domains or subdomains instead of slugs.

## 1.1.0 (2019-03-19)

- Add option to remove the main locale slug from the URL.

## 1.0.1 (2019-03-13)

- Register the `UrlGenerator` the same way Laravel does in recent versions

## 1.0.0 (2018-04-03)

- Automatically register a route for each locale you wish to support.
- Generate localized route URL's in the simplest way using the `route()` helper.
- Redirect to localized routes using the `redirect()->route()` helper.
- Allow routes to be cached.
- Let you work with routes without thinking too much about locales.
- Optionally translate each segment in your URI's.
