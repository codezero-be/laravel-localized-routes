<?php

namespace CodeZero\LocalizedRoutes;

use CodeZero\Localizer\Localizer;
use Illuminate\Support\Facades\App;

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
     * Create a new SetLocale instance.
     *
     * @param array $supportedLocales
     * @param \CodeZero\Localizer\Localizer $localizer
     */
    public function __construct(array $supportedLocales, Localizer $localizer = null)
    {
        $this->supportedLocales = $supportedLocales;
        $this->localizer = $localizer;
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
        if ( ! $this->localizer) return false;

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
        $this->localizer
            ? $this->localizer->store($locale)
            : App::setLocale($locale);
    }
}
