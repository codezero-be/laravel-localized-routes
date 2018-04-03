<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit\Macros;

use App;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use Lang;

class UriTranslationMacroTest extends TestCase
{
    protected function setTranslations($translations)
    {
        // Fake that we created a routes.php file in
        // 'resources/lang/' for each language
        // with the given translations.
        Lang::setLoaded([
            '*' => [
                'routes' => $translations
            ]
        ]);
    }

    /** @test */
    public function it_translates_every_segment_in_a_uri_to_the_current_locale()
    {
        $this->setTranslations([
            'nl' => [
                'my' => 'mijn',
                'new' => 'nieuwe',
                'page' => 'pagina',
            ]
        ]);

        App::setLocale('en');
        $this->assertEquals('my/new/page', Lang::uri('my/new/page'));

        App::setLocale('nl');
        $this->assertEquals('mijn/nieuwe/pagina', Lang::uri('my/new/page'));
    }

    /** @test */
    public function it_translates_every_segment_in_a_uri_to_the_given_locale()
    {
        $this->setTranslations([
            'nl' => [
                'my' => 'mijn',
                'new' => 'nieuwe',
                'page' => 'pagina',
            ]
        ]);

        $this->assertEquals('mijn/nieuwe/pagina', Lang::uri('my/new/page', 'nl'));
    }

    /** @test */
    public function it_uses_the_original_values_if_a_translation_does_not_exist()
    {
        $this->setTranslations([
            'nl' => [
                'my' => 'mijn',
                'new' => 'nieuwe',
            ]
        ]);

        $this->assertEquals('mijn/nieuwe/page', Lang::uri('my/new/page', 'nl'));
        $this->assertEquals('my/new/page', Lang::uri('my/new/page', 'fr'));
    }

    /** @test */
    public function it_ignores_trailing_slashes()
    {
        $this->setTranslations([
            'nl' => [
                'my' => 'mijn',
                'new' => 'nieuwe',
                'page' => 'pagina',
            ]
        ]);

        $this->assertEquals('mijn/nieuwe/pagina', Lang::uri('/my/new/page/', 'nl'));
    }

    /** @test */
    public function it_skips_placeholders_in_a_uri()
    {
        $this->setTranslations([
            'nl' => [
                'articles' => 'artikels',
            ]
        ]);

        $this->assertEquals('artikels/{article}', Lang::uri('articles/{article}', 'nl'));
    }
}
