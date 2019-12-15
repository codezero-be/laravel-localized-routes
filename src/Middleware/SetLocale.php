<?php

namespace CodeZero\LocalizedRoutes\Middleware;

use Closure;
use CodeZero\Localizer\Localizer;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class SetLocale
{
    /**
     * Localizer.
     *
     * @var \CodeZero\Localizer\Localizer
     */
    protected $localizer;

    /**
     * Create a new SetLocale instance.
     *
     * @param \CodeZero\Localizer\Localizer $localizer
     */
    public function __construct(Localizer $localizer)
    {
        $this->localizer = $localizer;
    }

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
        $locale = $request->route()->getAction('localized-routes-locale');

        if ( ! $locale && $this->shouldUseLocalizer()) {
            $locale = $this->detectLocales();
        }

        if ($locale && $this->shouldUseLocalizer()) {
            $this->localizer->store($locale);
        }

        if ($locale && ! $this->shouldUseLocalizer()) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    /**
     * Detect locales.
     *
     * @return string|false
     */
    public function detectLocales()
    {
        $supportedLocales = Config::get('localized-routes.supported-locales', []);

        $this->localizer->setSupportedLocales($supportedLocales);

        return $this->localizer->detect();
    }

    /**
     * Check if the 'use_localizer' option is enabled.
     *
     * @return bool
     */
    protected function shouldUseLocalizer()
    {
        return Config::get('localized-routes.use_localizer', false);
    }
}
