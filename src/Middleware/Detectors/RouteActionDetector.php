<?php

namespace CodeZero\LocalizedRoutes\Middleware\Detectors;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

class RouteActionDetector implements Detector
{
    /**
     * Detect the locale.
     *
     * @return string|array|null
     */
    public function detect()
    {
        $action = Config::get('localized-routes.route_action');

        return Request::route()->getAction($action);
    }
}
