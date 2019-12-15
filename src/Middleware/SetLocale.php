<?php

namespace CodeZero\LocalizedRoutes\Middleware;

use Closure;
use CodeZero\Localizer\Localizer;
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
        // If it is present, set it as the locale with 'codezero/laravel-localizer'.
        // If not, auto-detect the locale with 'codezero/laravel-localizer'
        $locale = $request->route()->getAction('localized-routes-locale') ?: $this->detectLocales();

        if ($locale) {
            $this->localizer->store($locale);
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
}
