<?php

namespace CodeZero\LocalizedRoutes\Middleware;

use Closure;
use CodeZero\LocalizedRoutes\LocaleHandler;
use CodeZero\Localizer\Detectors\UrlDetector;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // This package sets a custom route attribute for the locale.
        // If it is present, use this as the locale.
        $locale = $request->route()->getAction('localized-routes-locale')
            ?: $this->getLocaleFromFallbackRoute($request);

        App::make(LocaleHandler::class)->handleLocale($locale);

        return $next($request);
    }

    /**
     * Get locale from fallback route.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string|null
     */
    protected function getLocaleFromFallbackRoute(\Illuminate\Http\Request $request)
    {
        if ( ! $request->route()->isFallback) {
            return null;
        }

        $locale = App::make(UrlDetector::class)->detect();
        $localeIsSupported = in_array($locale, $this->getSupportedLocales());
        $omittedLocale = Config::get('localized-routes.omit_url_prefix_for_locale');

        if ( ! $localeIsSupported && $omittedLocale) {
            return $omittedLocale;
        }

        if ( ! $localeIsSupported) {
            return null;
        }

        return $locale;
    }

    /**
     * Get the supported locales and not the custom domains.
     *
     * @return array
     */
    protected function getSupportedLocales()
    {
        $locales = Config::get('localized-routes.supported-locales', []);
        $keys = array_keys($locales);

        if ( ! empty($locales) && is_numeric($keys[0])) {
            return $locales;
        }

        return $keys;
    }
}
