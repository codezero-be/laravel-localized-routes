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
     * The custom route action where we will set the locale of
     * the routes registered within the Route::localized() closure.
     */
    'route_action' => 'locale',

];
