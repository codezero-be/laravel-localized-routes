<?php

namespace CodeZero\LocalizedRoutes\Middleware;

use Closure;
use CodeZero\LocalizedRoutes\Facades\LocaleConfig;
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
        $this->configureLocalizer();

        $localizer = App::make(\CodeZero\Localizer\Middleware\SetLocale::class);

        return $localizer->handle($request, $next);
    }

    /**
     * Copy the essential configuration options to Localizer.
     *
     * @return void
     */
    protected function configureLocalizer()
    {
        Config::set('localizer.supported_locales', LocaleConfig::getSupportedLocales());
        Config::set('localizer.omitted_locale', LocaleConfig::getOmittedLocale());
        Config::set('localizer.route_action', LocaleConfig::getRouteAction());
        Config::set('localizer.trusted_detectors', [
            \CodeZero\Localizer\Detectors\RouteActionDetector::class
        ]);
    }
}
