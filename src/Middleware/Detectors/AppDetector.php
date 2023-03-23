<?php

namespace CodeZero\LocalizedRoutes\Middleware\Detectors;

use Illuminate\Support\Facades\App;

class AppDetector implements Detector
{
    /**
     * Detect the locale.
     *
     * @return string|array|null
     */
    public function detect()
    {
        return App::getLocale();
    }
}
