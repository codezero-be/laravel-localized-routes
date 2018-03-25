<?php

namespace CodeZero\LocalizedRoutes\Tests\Unit;

use CodeZero\LocalizedRoutes\Tests\TestCase;
use CodeZero\LocalizedRoutes\UrlGenerator;

class RedirectorTest extends TestCase
{
    /** @test */
    public function it_uses_our_url_generator_class()
    {
        $this->assertInstanceOf(UrlGenerator::class, redirect()->getUrlGenerator());
    }
}
