<?php

namespace CodeZero\LocalizedRoutes\Middleware\Detectors;

use CodeZero\LocalizedRoutes\Facades\LocaleConfig;
use Illuminate\Support\Facades\Request;

class UrlDetector implements Detector
{
    /**
     * Detect the locale.
     *
     * @return string|array|null
     */
    public function detect()
    {
        $slug = Request::segment(1);

        // If supported locales is a simple array like ['en', 'nl']
        // just return the slug and let the calling code check if it is supported.
        if ( ! LocaleConfig::hasLocales() || LocaleConfig::hasSimpleLocales()) {
            return $slug;
        }

        // Find the locale that belongs to the custom domain or slug.
        // Return the original slug as fallback.
        // The calling code should validate and handle it.
        $domain = Request::getHttpHost();
        $locale = LocaleConfig::findLocaleByDomain($domain) ?? LocaleConfig::findLocaleBySlug($slug) ?? $slug;

        return $locale;
    }
}
