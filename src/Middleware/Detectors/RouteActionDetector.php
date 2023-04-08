<?php

namespace CodeZero\LocalizedRoutes\Middleware\Detectors;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class RouteActionDetector implements Detector
{
    /**
     * The current Route.
     *
     * @var \Illuminate\Routing\Route
     */
    protected $route;

    /**
     * Create a new RouteActionDetector instance.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->route = $request->route();
    }

    /**
     * Detect the locale.
     *
     * @return string|array|null
     */
    public function detect()
    {
        $action = Config::get('localized-routes.route_action');

        return $this->route->getAction($action);
    }
}
