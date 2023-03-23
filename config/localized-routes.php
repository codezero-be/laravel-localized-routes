<?php

return [

    /**
     * The locales you wish to support.
     */
    'supported_locales' => [],

    /**
     * The fallback locale to use when generating a route URL
     * and the provided locale is not supported.
     */
    'fallback_locale' => null,

    /**
     * If you have a main locale, and you want to omit
     * its slug from the URL, specify it here.
     */
    'omitted_locale' => null,

    /**
     * Set this option to true if you want to redirect URLs
     * without a locale slug to their localized version.
     * You need to register the fallback route for this to work.
     */
    'redirect_to_localized_urls' => false,

    /**
     * The status code when redirecting to localized URLs.
     * 301 - permanently
     * 302 - temporary
     */
    'redirect_status_code' => 301,

    /**
     * Set your custom 404 view. This view is localized.
     * If the view does not exist, a normal 404 will be thrown.
     * You need to register the fallback route for this to work.
     */
    '404_view' => 'errors.404',

    /**
     * The custom route action where we will set the locale of the routes
     * that are registered within the Route::localized() closure.
     * This can be detected by the RouteActionDetector.
     */
    'route_action' => 'locale',

    /**
     * The attribute on the user model that holds the locale,
     * when using the UserDetector.
     */
    'user_attribute' => 'locale',

    /**
     * The session key that holds the locale,
     * when using the SessionDetector and SessionStore.
     */
    'session_key' => 'locale',

    /**
     * The name of the cookie that holds the locale,
     * when using the CookieDetector and CookieStore.
     */
    'cookie_name' => 'locale',

    /**
     * The lifetime of the cookie that holds the locale,
     * when using the CookieStore.
     */
    'cookie_minutes' => 60 * 24 * 365, // 1 year

    /**
     * The detectors to use to find a matching locale.
     * These will be executed in the order that they are added to the array!
     */
    'detectors' => [
        CodeZero\LocalizedRoutes\Middleware\Detectors\RouteActionDetector::class, //=> required for scoped config
        CodeZero\LocalizedRoutes\Middleware\Detectors\UrlDetector::class, //=> required
        CodeZero\LocalizedRoutes\Middleware\Detectors\OmittedLocaleDetector::class, //=> required for omitted locale
        CodeZero\LocalizedRoutes\Middleware\Detectors\UserDetector::class,
        CodeZero\LocalizedRoutes\Middleware\Detectors\SessionDetector::class,
        CodeZero\LocalizedRoutes\Middleware\Detectors\CookieDetector::class,
        CodeZero\LocalizedRoutes\Middleware\Detectors\BrowserDetector::class,
        CodeZero\LocalizedRoutes\Middleware\Detectors\AppDetector::class, //=> required
    ],

    /**
     * Add any of the above detector class names here to make it trusted.
     * When a trusted detector returns a locale, it will be used
     * as the app locale, regardless if it's a supported locale or not.
     */
    'trusted_detectors' => [
        CodeZero\LocalizedRoutes\Middleware\Detectors\RouteActionDetector::class //=> required for scoped config
    ],

    /**
     * The stores to store the first matching locale in.
     */
    'stores' => [
        CodeZero\LocalizedRoutes\Middleware\Stores\SessionStore::class,
        CodeZero\LocalizedRoutes\Middleware\Stores\CookieStore::class,
        CodeZero\LocalizedRoutes\Middleware\Stores\AppStore::class, //=> required
    ],

];
