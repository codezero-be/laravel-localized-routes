<?php

namespace CodeZero\LocalizedRoutes;

class LocaleConfig
{
    /**
     * The configured supported locales.
     *
     * @var array
     */
    protected $supportedLocales;

    /**
     * The configured omitted locale.
     *
     * @var string|null
     */
    protected $omittedLocale;

    /**
     * The configured fallback locale.
     *
     * @var string|null
     */
    protected $fallbackLocale;

    /**
     * The configured route action that holds a route's locale.
     *
     * @var string
     */
    protected $routeAction;

    /**
     * Create a new LocaleConfig instance.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->supportedLocales = $config['supported_locales'] ?? [];
        $this->omittedLocale = $config['omitted_locale'] ?? null;
        $this->fallbackLocale = $config['fallback_locale'] ?? null;
        $this->routeAction = $config['route_action'] ?? null;
    }

    /**
     * Get the configured supported locales.
     *
     * @return array
     */
    public function getSupportedLocales()
    {
        return $this->supportedLocales;
    }

    /**
     * Set the supported locales.
     *
     * @param array $locales
     *
     * @return void
     */
    public function setSupportedLocales($locales)
    {
        $this->supportedLocales = $locales;
    }

    /**
     * Get the locale that should be omitted in the URI path.
     *
     * @return string|null
     */
    public function getOmittedLocale()
    {
        return $this->omittedLocale;
    }

    /**
     * Set the locale that should be omitted in the URI path.
     *
     * @param string|null $locale
     *
     * @return void
     */
    public function setOmittedLocale($locale)
    {
        $this->omittedLocale = $locale;
    }

    /**
     * Get the fallback locale.
     *
     * @return string|null
     */
    public function getFallbackLocale()
    {
        return $this->fallbackLocale;
    }

    /**
     * Set the fallback locale.
     *
     * @param string|null $locale
     *
     * @return void
     */
    public function setFallbackLocale($locale)
    {
        $this->fallbackLocale = $locale;
    }

    /**
     * Get the route action that holds a route's locale.
     *
     * @return string
     */
    public function getRouteAction()
    {
        return $this->routeAction;
    }

    /**
     * Set the route action that holds a route's locale.
     *
     * @param string $locale
     *
     * @return string
     */
    public function setRouteAction($locale)
    {
        return $this->routeAction = $locale;
    }

    /**
     * Get the locales (not the slugs or domains).
     *
     * @return array
     */
    public function getLocales()
    {
        $locales = $this->getSupportedLocales();

        if ($this->hasSimpleLocales()) {
            return $locales;
        }

        return array_keys($locales);
    }

    /**
     * Check if there are any locales configured.
     *
     * @return bool
     */
    public function hasLocales()
    {
        return count($this->getSupportedLocales()) > 0;
    }

    /**
     * Check if there are only locales configured,
     * and not custom slugs or domains.
     *
     * @return bool
     */
    public function hasSimpleLocales()
    {
        return is_numeric(key($this->getSupportedLocales()));
    }

    /**
     * Check if custom slugs are configured.
     *
     * @return bool
     */
    public function hasCustomSlugs()
    {
        return $this->hasLocales() && ! $this->hasSimpleLocales() && ! $this->hasCustomDomains();
    }

    /**
     * Check if custom domains are configured.
     *
     * @return bool
     */
    public function hasCustomDomains()
    {
        $firstValue = array_values($this->getSupportedLocales())[0] ?? '';
        $containsDot =  strpos($firstValue, '.') !== false;

        return $containsDot;
    }

    /**
     * Check if the given locale is supported.
     *
     * @param string $locale
     *
     * @return bool
     */
    public function isSupportedLocale($locale)
    {
        return in_array($locale, $this->getLocales());
    }
}
