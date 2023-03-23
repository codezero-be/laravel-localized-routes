<?php

namespace CodeZero\LocalizedRoutes\Middleware\Detectors;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;

class CookieDetector implements Detector
{
    /**
     * Detect the locale.
     *
     * @return string|array|null
     */
    public function detect()
    {
        $key = Config::get('localized-routes.cookie_name');

        return Cookie::get($key);
    }
}
