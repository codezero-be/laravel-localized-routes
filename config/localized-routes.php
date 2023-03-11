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

];
