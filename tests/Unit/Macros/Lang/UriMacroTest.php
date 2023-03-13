<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit\Macros\Lang;

use CodeZero\LocalizedRoutes\Tests\TestCase;
use Illuminate\Support\Facades\Lang;

class UriMacroTest extends TestCase
{
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

        $this->setAppLocale('en');
        $this->assertEquals('my/new/page', Lang::uri('my/new/page'));

        $this->setAppLocale('nl');
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

    /** @test */
    public function you_can_translate_a_full_uri()
    {
        $this->setTranslations([
            'nl' => [
                'glass'          => 'glas',
                'products'       => 'producten',
                'products/glass' => 'producten/glazen'
            ]
        ]);

        $this->assertEquals('producten/glazen', Lang::uri('products/glass', 'nl'));
    }

    /** @test */
    public function you_can_translate_a_full_uri_with_placeholder()
    {
        $this->setTranslations([
            'nl' => [
                'glass'                => 'glas',
                'products'             => 'producten',
                'products/glass/{type}' => 'producten/glazen/{type}'
            ]
        ]);

        $this->assertEquals('producten/glazen/{type}', Lang::uri('products/glass/{type}', 'nl'));
    }

    /** @test */
    public function you_can_specify_a_namespace()
    {
        $this->setTranslations([
            'nl' => [
                'articles' => 'artikels',
            ]
        ], 'blog');

        $this->assertEquals('artikels/{article}', Lang::uri('articles/{article}', 'nl', 'blog'));
    }
}
