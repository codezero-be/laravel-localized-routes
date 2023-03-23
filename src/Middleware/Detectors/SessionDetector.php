<?php

namespace CodeZero\LocalizedRoutes\Middleware\Detectors;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class SessionDetector implements Detector
{
    /**
     * Detect the locale.
     *
     * @return string|array|null
     */
    public function detect()
    {
        $key = Config::get('localized-routes.session_key');

        return Session::get($key);
    }
}
