<?php

return [

    /**
     * The locales you wish to support.
     */
    'supported-locales' => [],

    /**
     * The fallback locale to use when a provided locale is not supported.
     */
    'fallback_locale' => null,

    /**
     * If you have a main locale and don't want
     * to prefix it in the URL, specify it here.
     *
     * 'omit_url_prefix_for_locale' => 'en',
     */
    'omit_url_prefix_for_locale' => null,

    /**
     * Set this option to true if you want to redirect
     * unlocalized URL's to their localized version.
     * You need to register the fallback route for this to work.
     */
    'redirect_to_localized_urls' => false,

    /**
     * The status code when redirecting to localized URL's.
     * 301 - permanently
     * 302 - temporary
     */
    'redirect_status_code' => 301,

    /**
     * Set your custom 404 view.
     * This view is localized.
     * If the view does not exist, a normal 404 will be thrown.
     * You need to register the fallback route for this to work.
     */
    '404_view' => 'errors.404',

    /**
     * If you want to automatically set the locale
     * for localized routes set this to true.
     */
    'use_locale_middleware' => false,

    /**
     * If true, this package will use 'codezero/laravel-localizer'
     * to detect and set the preferred supported locale.
     *
     * For non-localized routes, it will look for a locale in the URL,
     * in the session, in a cookie, in the browser or in the app config.
     * This can be very useful if you have a generic home page.
     *
     * If a locale is detected, it will be stored in the session,
     * in a cookie and as the app locale.
     *
     * If you disable this option, only localized routes will have a locale
     * and only the app locale will be set (so not in the session or cookie).
     *
     * You can publish its config file and tweak it for your needs.
     * This package will only override its 'supported-locales' option
     * with the 'supported-locales' option in this file.
     *
     * For more info, visit:
     * https://github.com/codezero-be/laravel-localizer
     *
     * This option only has effect if you use the SetLocale middleware.
     */
    'use_localizer' => false,

    /**
     * Map application locale to a custom route prefix.
     *
     * 'custom_prefixes' => [
     *   'en' => 'english',
     *   'nl' => 'dutch',
     * ]
     */
    'custom_prefixes' => [],

];
