<?php

namespace CodeZero\LocalizedRoutes\Middleware\Detectors;

interface Detector
{
    /**
     * Detect the locale.
     *
     * @return string|array|null
     */
    public function detect();
}
