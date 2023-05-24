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
     * @var string|null
     */
    protected $routeAction;

    /**
     * Create a new LocaleConfig instance.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
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
    public function getSupportedLocales(): array
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
    public function setSupportedLocales(array $locales): void
    {
        $this->supportedLocales = $locales;
    }

    /**
     * Get the locale that should be omitted in the URI path.
     *
     * @return string|null
     */
    public function getOmittedLocale(): ?string
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
    public function setOmittedLocale(?string $locale): void
    {
        $this->omittedLocale = $locale;
    }

    /**
     * Get the fallback locale.
     *
     * @return string|null
     */
    public function getFallbackLocale(): ?string
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
    public function setFallbackLocale(?string $locale): void
    {
        $this->fallbackLocale = $locale;
    }

    /**
     * Get the route action that holds a route's locale.
     *
     * @return string|null
     */
    public function getRouteAction(): ?string
    {
        return $this->routeAction;
    }

    /**
     * Set the route action that holds a route's locale.
     *
     * @param string $action
     *
     * @return string
     */
    public function setRouteAction(string $action): string
    {
        return $this->routeAction = $action;
    }

    /**
     * Get the locales (not the slugs or domains).
     *
     * @return array
     */
    public function getLocales(): array
    {
        $locales = $this->getSupportedLocales();

        if ($this->hasSimpleLocales()) {
            return $locales;
        }

        return array_keys($locales);
    }

    /**
     * Find the slug that belongs to the given locale.
     *
     * @param string $locale
     *
     * @return string|null
     */
    public function findSlugByLocale(string $locale): ?string
    {
        if ( ! $this->isSupportedLocale($locale) || $this->hasCustomDomains()) {
            return null;
        }

        return $this->getSupportedLocales()[$locale] ?? $locale;
    }

    /**
     * Find the domain that belongs to the given locale.
     *
     * @param string $locale
     *
     * @return string|null
     */
    public function findDomainByLocale(string $locale): ?string
    {
        if ( ! $this->isSupportedLocale($locale) || ! $this->hasCustomDomains()) {
            return null;
        }

        return $this->getSupportedLocales()[$locale];
    }

    /**
     * Find the locale that belongs to the given slug.
     *
     * @param ?string $slug
     *
     * @return string|null
     */
    public function findLocaleBySlug(?string $slug): ?string
    {
        if ($this->hasCustomDomains()) {
            return null;
        }

        if ($this->hasSimpleLocales() && $this->isSupportedLocale($slug)) {
            return $slug;
        }

        return array_search($slug, $this->getSupportedLocales()) ?: null;
    }

    /**
     * Find the locale that belongs to the given domain.
     *
     * @param string $domain
     *
     * @return string|null
     */
    public function findLocaleByDomain(string $domain): ?string
    {
        if ( ! $this->hasCustomDomains()) {
            return null;
        }

        return array_search($domain, $this->getSupportedLocales()) ?: null;
    }

    /**
     * Check if there are any locales configured.
     *
     * @return bool
     */
    public function hasLocales(): bool
    {
        return count($this->getSupportedLocales()) > 0;
    }

    /**
     * Check if there are only locales configured,
     * and not custom slugs or domains.
     *
     * @return bool
     */
    public function hasSimpleLocales(): bool
    {
        return is_numeric(key($this->getSupportedLocales()));
    }

    /**
     * Check if custom slugs are configured.
     *
     * @return bool
     */
    public function hasCustomSlugs(): bool
    {
        return $this->hasLocales() && ! $this->hasSimpleLocales() && ! $this->hasCustomDomains();
    }

    /**
     * Check if custom domains are configured.
     *
     * @return bool
     */
    public function hasCustomDomains(): bool
    {
        $firstValue = array_values($this->getSupportedLocales())[0] ?? '';
        $containsDot =  strpos($firstValue, '.') !== false;

        return $containsDot;
    }

    /**
     * Check if the given locale is supported.
     *
     * @param string|null $locale
     *
     * @return bool
     */
    public function isSupportedLocale(?string $locale): bool
    {
        return in_array($locale, $this->getLocales());
    }
}
