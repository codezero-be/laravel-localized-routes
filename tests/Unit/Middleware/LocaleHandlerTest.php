<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit\Middleware;

use CodeZero\LocalizedRoutes\Middleware\Detectors\Detector;
use CodeZero\LocalizedRoutes\Middleware\LocaleHandler;
use CodeZero\LocalizedRoutes\Middleware\Stores\Store;
use CodeZero\LocalizedRoutes\Tests\TestCase;
use Illuminate\Support\Facades\App;
use Mockery;

class LocaleHandlerTest extends TestCase
{
    /** @test */
    public function it_loops_through_the_detectors_and_returns_the_first_supported_locale()
    {
        $supportedLocales = ['en', 'nl'];
        $detectors = [
            Mockery::mock(Detector::class)->allows()->detect()->andReturns('de')->getMock(),
            Mockery::mock(Detector::class)->allows()->detect()->andReturns('nl')->getMock(),
            Mockery::mock(Detector::class)->allows()->detect()->andReturns('en')->getMock(),
        ];

        $localeHandler = new LocaleHandler($supportedLocales, $detectors);

        $this->assertEquals('nl', $localeHandler->detect());
    }

    /** @test */
    public function it_returns_the_first_match_if_an_array_of_locales_is_detected()
    {
        $supportedLocales = ['en', 'nl'];
        $detectors = [
            Mockery::mock(Detector::class)->allows()->detect()->andReturns(['de', 'nl', 'en'])->getMock(),
        ];

        $localeHandler = new LocaleHandler($supportedLocales, $detectors);

        $this->assertEquals('nl', $localeHandler->detect());
    }

    /** @test */
    public function trusted_detectors_ignore_supported_locales_and_may_set_any_locale()
    {
        $supportedLocales = ['en'];
        $detectors = [
            Mockery::mock(Detector::class)->allows()->detect()->andReturns('nl')->getMock(),
        ];
        $trustedDetectors = [
            Detector::class,
        ];

        $localeHandler = new LocaleHandler($supportedLocales, $detectors, [], $trustedDetectors);

        $this->assertEquals('nl', $localeHandler->detect());
    }

    /** @test */
    public function it_skips_null_and_false_and_empty_values()
    {
        App::instance(Detector::class, Mockery::mock(Detector::class)->allows()->detect()->andReturns('')->getMock());

        $supportedLocales = ['nl'];
        $detectors = [
            Detector::class,
            Mockery::mock(Detector::class)->allows()->detect()->andReturns(null)->getMock(),
            Mockery::mock(Detector::class)->allows()->detect()->andReturns(false)->getMock(),
            Mockery::mock(Detector::class)->allows()->detect()->andReturns('')->getMock(),
            Mockery::mock(Detector::class)->allows()->detect()->andReturns([])->getMock(),
            Mockery::mock(Detector::class)->allows()->detect()->andReturns('nl')->getMock(),
        ];

        $localeHandler = new LocaleHandler($supportedLocales, $detectors);

        $this->assertEquals('nl', $localeHandler->detect());
    }

    /** @test */
    public function it_skips_null_and_false_and_empty_values_from_trusted_detectors()
    {
        App::instance(Detector::class, Mockery::mock(Detector::class)->allows()->detect()->andReturns('')->getMock());

        $supportedLocales = ['en'];
        $detectors = [
            Detector::class,
            Mockery::mock(Detector::class)->allows()->detect()->andReturns(null)->getMock(),
            Mockery::mock(Detector::class)->allows()->detect()->andReturns(false)->getMock(),
            Mockery::mock(Detector::class)->allows()->detect()->andReturns('')->getMock(),
            Mockery::mock(Detector::class)->allows()->detect()->andReturns([])->getMock(),
            Mockery::mock(Detector::class)->allows()->detect()->andReturns('nl')->getMock(),
        ];
        $trustedDetectors = [
            Detector::class,
        ];

        $localeHandler = new LocaleHandler($supportedLocales, $detectors, [], $trustedDetectors);

        $this->assertEquals('nl', $localeHandler->detect());
    }

    /** @test */
    public function it_returns_false_if_no_supported_locale_could_be_detected()
    {
        $supportedLocales = ['en'];
        $detectors = [
            Mockery::mock(Detector::class)->allows()->detect()->andReturns('de')->getMock(),
            Mockery::mock(Detector::class)->allows()->detect()->andReturns('nl')->getMock(),
            Mockery::mock(Detector::class)->allows()->detect()->andReturns('fr')->getMock(),
        ];

        $localeHandler = new LocaleHandler($supportedLocales, $detectors);

        $this->assertNull($localeHandler->detect());
    }

    /** @test */
    public function it_loops_through_the_stores_and_calls_the_store_method_with_the_given_locale()
    {
        $stores = [
            Mockery::mock(Store::class)->expects()->store('nl')->once()->getMock(),
            Mockery::mock(Store::class)->expects()->store('nl')->once()->getMock(),
            Mockery::mock(Store::class)->expects()->store('nl')->once()->getMock(),
        ];

        $localeHandler = new LocaleHandler([], [], $stores);

        $localeHandler->store('nl');
    }

    /** @test */
    public function it_accepts_class_names_instead_of_instances_in_the_constructor()
    {
        App::instance(Store::class, Mockery::mock(Store::class)->expects()->store('nl')->once()->getMock());
        App::instance(Detector::class, Mockery::mock(Detector::class)->expects()->detect()->once()->getMock());

        $detectors = [Detector::class];
        $stores = [Store::class];

        $localeHandler = new LocaleHandler([], $detectors, $stores);

        $localeHandler->detect();
        $localeHandler->store('nl');
    }
}
