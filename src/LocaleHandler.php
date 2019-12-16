<?php

namespace CodeZero\LocalizedRoutes;

use CodeZero\Localizer\Localizer;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class LocaleHandler
{
    /**
     * Localizer.
     *
     * @var \CodeZero\Localizer\Localizer
     */
    protected $localizer;

    /**
     * Supported locales.
     *
     * @var array
     */
    protected $supportedLocales;

    /**
     * Should the Localizer package be used?
     *
     * @var bool
     */
    protected $shouldUseLocalizer;

    /**
     * Create a new SetLocale instance.
     *
     * @param \CodeZero\Localizer\Localizer $localizer
     */
    public function __construct(Localizer $localizer)
    {
        $this->localizer = $localizer;
        $this->supportedLocales = Config::get('localized-routes.supported-locales', []);
        $this->shouldUseLocalizer = Config::get('localized-routes.use_localizer', false);
    }

    /**
     * Detect and/or set the locale.
     *
     * @param string|null $locale
     *
     * @return void
     */
    public function handleLocale($locale)
    {
        $locale = $locale ?: $this->detectLocales();

        if ($locale) {
            $this->setLocale($locale);
        }
    }

    /**
     * Detect locales.
     *
     * @return string|false
     */
    protected function detectLocales()
    {
        if ( ! $this->shouldUseLocalizer) return false;

        $this->localizer->setSupportedLocales($this->supportedLocales);

        return $this->localizer->detect();
    }

    /**
     * Set the locale.
     *
     * @param string $locale
     *
     * @return void
     */
    protected function setLocale($locale)
    {
        $this->shouldUseLocalizer
            ? $this->localizer->store($locale)
            : App::setLocale($locale);
    }
}
