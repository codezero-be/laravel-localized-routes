<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use CodeZero\LocalizedRoutes\LocaleConfig;
use CodeZero\LocalizedRoutes\Tests\TestCase;

final class LocaleConfigTest extends TestCase
{
    #[Test]
    public function it_gets_the_supported_locales(): void
    {
        $config = new LocaleConfig(['supported_locales' => null]);
        $this->assertEquals([], $config->getSupportedLocales());

        $config = new LocaleConfig(['supported_locales' => ['en', 'nl']]);
        $this->assertEquals(['en', 'nl'], $config->getSupportedLocales());

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english', 'nl' => 'dutch']]);
        $this->assertEquals(['en' => 'english', 'nl' => 'dutch'], $config->getSupportedLocales());

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english.test', 'nl' => 'dutch.test']]);
        $this->assertEquals(['en' => 'english.test', 'nl' => 'dutch.test'], $config->getSupportedLocales());
    }

    #[Test]
    public function it_sets_the_supported_locales(): void
    {
        $config = new LocaleConfig(['supported_locales' => null]);
        $config->setSupportedLocales(['en' => 'english', 'nl' => 'dutch']);
        $this->assertEquals(['en' => 'english', 'nl' => 'dutch'], $config->getSupportedLocales());
    }

    #[Test]
    public function it_gets_the_omitted_locale(): void
    {
        $config = new LocaleConfig(['omitted_locale' => null]);
        $this->assertEquals(null, $config->getOmittedLocale());

        $config = new LocaleConfig(['omitted_locale' => 'en']);
        $this->assertEquals('en', $config->getOmittedLocale());
    }

    #[Test]
    public function it_sets_the_omitted_locale(): void
    {
        $config = new LocaleConfig(['omitted_locale' => null]);
        $config->setOmittedLocale('en');
        $this->assertEquals('en', $config->getOmittedLocale());
    }

    #[Test]
    public function it_gets_the_fallback_locale(): void
    {
        $config = new LocaleConfig(['fallback_locale' => null]);
        $this->assertEquals(null, $config->getFallbackLocale());

        $config = new LocaleConfig(['fallback_locale' => 'en']);
        $this->assertEquals('en', $config->getFallbackLocale());
    }

    #[Test]
    public function it_sets_the_fallback_locale(): void
    {
        $config = new LocaleConfig(['fallback_locale' => null]);
        $config->setFallbackLocale('en');
        $this->assertEquals('en', $config->getFallbackLocale());
    }

    #[Test]
    public function it_gets_the_route_action(): void
    {
        $config = new LocaleConfig(['route_action' => null]);
        $this->assertEquals(null, $config->getRouteAction());

        $config = new LocaleConfig(['route_action' => 'locale']);
        $this->assertEquals('locale', $config->getRouteAction());
    }

    #[Test]
    public function it_sets_the_route_action(): void
    {
        $config = new LocaleConfig(['route_action' => null]);
        $config->setRouteAction('locale');
        $this->assertEquals('locale', $config->getRouteAction());
    }

    #[Test]
    public function it_gets_the_locales(): void
    {
        $config = new LocaleConfig(['supported_locales' => null]);
        $this->assertEquals([], $config->getLocales());

        $config = new LocaleConfig(['supported_locales' => ['en', 'nl']]);
        $this->assertEquals(['en', 'nl'], $config->getLocales());

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english', 'nl' => 'dutch']]);
        $this->assertEquals(['en', 'nl'], $config->getLocales());

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english.test', 'nl' => 'dutch.test']]);
        $this->assertEquals(['en', 'nl'], $config->getLocales());
    }

    #[Test]
    public function it_finds_a_slug_by_its_locale(): void
    {
        $config = new LocaleConfig(['supported_locales' => null]);
        $this->assertEquals(null, $config->findSlugByLocale('en'));

        $config = new LocaleConfig(['supported_locales' => ['en']]);
        $this->assertEquals(null, $config->findSlugByLocale('nl'));

        $config = new LocaleConfig(['supported_locales' => ['en']]);
        $this->assertEquals('en', $config->findSlugByLocale('en'));

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english']]);
        $this->assertEquals('english', $config->findSlugByLocale('en'));

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english.test']]);
        $this->assertEquals(null, $config->findSlugByLocale('en'));
    }

    #[Test]
    public function it_finds_a_domain_by_its_locale(): void
    {
        $config = new LocaleConfig(['supported_locales' => null]);
        $this->assertEquals(null, $config->findDomainByLocale('en'));

        $config = new LocaleConfig(['supported_locales' => ['en']]);
        $this->assertEquals(null, $config->findDomainByLocale('nl'));
        $this->assertEquals(null, $config->findDomainByLocale('en'));

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english']]);
        $this->assertEquals(null, $config->findDomainByLocale('en'));

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english.test']]);
        $this->assertEquals('english.test', $config->findDomainByLocale('en'));
    }

    #[Test]
    public function it_finds_a_locale_by_its_slug(): void
    {
        $config = new LocaleConfig(['supported_locales' => null]);
        $this->assertEquals(null, $config->findLocaleBySlug('en'));

        $config = new LocaleConfig(['supported_locales' => ['en']]);
        $this->assertEquals(null, $config->findLocaleBySlug('nl'));
        $this->assertEquals('en', $config->findLocaleBySlug('en'));

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english']]);
        $this->assertEquals(null, $config->findLocaleBySlug('en'));
        $this->assertEquals('en', $config->findLocaleBySlug('english'));

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english.test']]);
        $this->assertEquals(null, $config->findLocaleBySlug('en'));
        $this->assertEquals(null, $config->findLocaleBySlug('english.test'));
    }

    #[Test]
    public function it_finds_a_locale_by_its_domain(): void
    {
        $config = new LocaleConfig(['supported_locales' => null]);
        $this->assertEquals(null, $config->findLocaleByDomain('english.test'));

        $config = new LocaleConfig(['supported_locales' => ['en']]);
        $this->assertEquals(null, $config->findLocaleByDomain('nl'));
        $this->assertEquals(null, $config->findLocaleByDomain('en'));

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english']]);
        $this->assertEquals(null, $config->findLocaleByDomain('en'));
        $this->assertEquals(null, $config->findLocaleByDomain('english'));

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english.test']]);
        $this->assertEquals(null, $config->findLocaleByDomain('en'));
        $this->assertEquals('en', $config->findLocaleByDomain('english.test'));
    }

    #[Test]
    public function it_checks_if_there_are_any_locales_configured(): void
    {
        $config = new LocaleConfig(['supported_locales' => null]);
        $this->assertFalse($config->hasLocales());

        $config = new LocaleConfig(['supported_locales' => ['en', 'nl']]);
        $this->assertTrue($config->hasLocales());

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english', 'nl' => 'dutch']]);
        $this->assertTrue($config->hasLocales());

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english.test', 'nl' => 'dutch.test']]);
        $this->assertTrue($config->hasLocales());
    }

    #[Test]
    public function it_checks_if_custom_slugs_are_configured(): void
    {
        $config = new LocaleConfig(['supported_locales' => null]);
        $this->assertFalse($config->hasCustomSlugs());

        $config = new LocaleConfig(['supported_locales' => ['en', 'nl']]);
        $this->assertFalse($config->hasCustomSlugs());

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english', 'nl' => 'dutch']]);
        $this->assertTrue($config->hasCustomSlugs());

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english.test', 'nl' => 'dutch.test']]);
        $this->assertFalse($config->hasCustomSlugs());
    }

    #[Test]
    public function it_checks_if_custom_domains_are_configured(): void
    {
        $config = new LocaleConfig(['supported_locales' => null]);
        $this->assertFalse($config->hasCustomDomains());

        $config = new LocaleConfig(['supported_locales' => ['en', 'nl']]);
        $this->assertFalse($config->hasCustomDomains());

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english', 'nl' => 'dutch']]);
        $this->assertFalse($config->hasCustomDomains());

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english.test', 'nl' => 'dutch.test']]);
        $this->assertTrue($config->hasCustomDomains());
    }

    #[Test]
    public function it_checks_if_a_locale_is_supported(): void
    {
        $config = new LocaleConfig(['supported_locales' => null]);
        $this->assertFalse($config->isSupportedLocale(null));
        $this->assertFalse($config->isSupportedLocale('en'));

        $config = new LocaleConfig(['supported_locales' => ['en']]);
        $this->assertFalse($config->isSupportedLocale('nl'));
        $this->assertTrue($config->isSupportedLocale('en'));

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english']]);
        $this->assertFalse($config->isSupportedLocale('english'));
        $this->assertTrue($config->isSupportedLocale('en'));

        $config = new LocaleConfig(['supported_locales' => ['en' => 'english.test']]);
        $this->assertFalse($config->isSupportedLocale('english.test'));
        $this->assertTrue($config->isSupportedLocale('en'));
    }
}
