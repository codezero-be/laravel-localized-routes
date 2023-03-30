<?php

namespace CodeZero\LocalizedRoutes\Middleware;

use Illuminate\Support\Facades\App;

class LocaleHandler
{
    /**
     * Supported locales.
     *
     * @var \Illuminate\Support\Collection|array
     */
    protected $locales;

    /**
     * \CodeZero\LocalizedRoutes\Middleware\Detectors\Detector class names or instances.
     *
     * @var \Illuminate\Support\Collection|array
     */
    protected $detectors;

    /**
     * \CodeZero\LocalizedRoutes\Middleware\Stores\Store class names or instances.
     *
     * @var \Illuminate\Support\Collection|array
     */
    protected $stores;

    /**
     * \CodeZero\LocalizedRoutes\Middleware\Detectors\Detector class names.
     *
     * @var \Illuminate\Support\Collection|array
     */
    protected $trustedDetectors;

    /**
     * Create a new LocaleHandler instance.
     *
     * @param \Illuminate\Support\Collection|array $locales
     * @param \Illuminate\Support\Collection|array $detectors
     * @param \Illuminate\Support\Collection|array $stores
     * @param \Illuminate\Support\Collection|array $trustedDetectors
     */
    public function __construct($locales, $detectors, $stores = [], $trustedDetectors = [])
    {
        $this->locales = $locales;
        $this->detectors = $detectors;
        $this->stores = $stores;
        $this->trustedDetectors = $trustedDetectors;
    }

    /**
     * Detect any supported locale and return the first match.
     *
     * @return string|null
     */
    public function detect(): ?string
    {
        foreach ($this->detectors as $detector) {
            $locales = (array) $this->getInstance($detector)->detect();

            foreach ($locales as $locale) {
                if ($locale && ($this->isSupportedLocale($locale) || $this->isTrustedDetector($detector))) {
                    return $locale;
                }
            }
        }

        return null;
    }

    /**
     * Store the given locale.
     *
     * @param string $locale
     *
     * @return void
     */
    public function store(string $locale): void
    {
        foreach ($this->stores as $store) {
            $this->getInstance($store)->store($locale);
        }
    }

    /**
     * Check if the given locale is supported.
     *
     * @param string|null $locale
     *
     * @return bool
     */
    protected function isSupportedLocale(?string $locale): bool
    {
        return in_array($locale, $this->locales);
    }

    /**
     * Check if the given Detector class is trusted.
     *
     * @param \CodeZero\LocalizedRoutes\Middleware\Detectors\Detector|string $detector
     *
     * @return bool
     */
    protected function isTrustedDetector($detector): bool
    {
        if (is_string($detector)) {
            return in_array($detector, $this->trustedDetectors);
        }

        foreach ($this->trustedDetectors as $trustedDetector) {
            if ($detector instanceof $trustedDetector) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the class from Laravel's IOC container if it is a string.
     *
     * @param mixed $class
     *
     * @return mixed
     */
    protected function getInstance($class)
    {
        if (is_string($class)) {
            return App::make($class);
        }

        return $class;
    }
}
