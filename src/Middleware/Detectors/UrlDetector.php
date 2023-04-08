<?php

namespace CodeZero\LocalizedRoutes\Middleware\Detectors;

use CodeZero\LocalizedRoutes\Facades\LocaleConfig;
use Illuminate\Http\Request;

class UrlDetector implements Detector
{
    /**
     * The current Request.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create a new UrlDetector instance.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Detect the locale.
     *
     * @return string|array|null
     */
    public function detect()
    {
        $slug = $this->request->segment(1);

        // If supported locales is a simple array like ['en', 'nl']
        // just return the slug and let the calling code check if it is supported.
        if ( ! LocaleConfig::hasLocales() || LocaleConfig::hasSimpleLocales()) {
            return $slug;
        }

        // Find the locale that belongs to the custom domain or slug.
        // Return the original slug as fallback.
        // The calling code should validate and handle it.
        $domain = $this->request->getHttpHost();
        $locale = LocaleConfig::findLocaleByDomain($domain) ?? LocaleConfig::findLocaleBySlug($slug) ?? $slug;

        return $locale;
    }
}
