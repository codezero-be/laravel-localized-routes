<?php

namespace CodeZero\LocalizedRoutes\Middleware;

use Closure;
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
        $supportedLocales = Config::get('localized-routes.supported_locales');
        $omittedLocale = Config::get('localized-routes.omitted_locale');
        $routeAction = Config::get('localized-routes.route_action');

        Config::set('localizer.supported_locales', $supportedLocales);
        Config::set('localizer.omitted_locale', $omittedLocale);
        Config::set('localizer.route_action', $routeAction);
        Config::set('localizer.trusted_detectors', [
            \CodeZero\Localizer\Detectors\RouteActionDetector::class
        ]);
    }
}
