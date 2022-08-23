<?php

namespace NFePHP\Gtin\Tests;

use NFePHP\Gtin\PrefixDefinition;
use PHPUnit\Framework\TestCase;

class PrefixDefinitionTest extends TestCase
{

    public function test_is_restricted(): void
    {
        $definition = new PrefixDefinition((object)[
            "nIni" => "000",
            "nFim" => "019",
            "restricted" => "0",
            "region" => "GS1 US"
        ]);
        $this->assertFalse($definition->isRestricted());
    }

    public function test_is_not_restricted(): void
    {
        $definition = new PrefixDefinition((object)[
            "nIni" => "020",
            "nFim" => "029",
            "restricted" => "1",
            "region" => "Números de circulação restrita dentro da região"
        ]);
        $this->assertTrue($definition->isRestricted());
    }

    public function test_get_region(): void
    {
        $definition = new PrefixDefinition((object)[
            "nIni" => "020",
            "nFim" => "029",
            "restricted" => "1",
            "region" => "Números de circulação restrita dentro da região"
        ]);
        $this->assertEquals("Números de circulação restrita dentro da região", $definition->getRegion());
    }

    public function testHasPrefix(): void
    {
        $definition = new PrefixDefinition((object)[
            "nIni" => "020",
            "nFim" => "029",
            "restricted" => "1",
            "region" => "Números de circulação restrita dentro da região"
        ]);

        $this->assertFalse($definition->hasPrefix("999"));
        $this->assertFalse($definition->hasPrefix("030"));
        $this->assertTrue($definition->hasPrefix("020"));
        $this->assertTrue($definition->hasPrefix("021"));
        $this->assertTrue($definition->hasPrefix("029"));
    }
}
