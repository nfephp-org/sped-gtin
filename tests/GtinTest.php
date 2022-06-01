<?php

namespace NFePHP\Gtin\Tests;

use NFePHP\Gtin\Gtin;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test Class
 *
 * @category  Library
 * @package   NFePHP\Gtin
 * @author    Roberto L. Machado <linux dot rlm at gmail dot com>
 * @copyright 2021 NFePHP Copyright (c)
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      http://github.com/nfephp-org/sped-gtin
 */
class GtinTest extends TestCase
{
    /**
     * Can instantiate class test
     *
     * @covers Gtin
     * @covers ::__contruct
     */
    public function testCanInstantiate(): void
    {
        $gtin = new Gtin('78935761');
        $this->assertInstanceOf('NFePHP\Gtin\Gtin', $gtin);
    }

    /**
     * Can instantiate static class test
     *
     * @covers Gtin
     * @covers ::__contruct
     * @covers ::check
     */
    public function testCanInstantiateStatic(): void
    {
        $gtin = Gtin::check('78935761');
        $this->assertInstanceOf('NFePHP\Gtin\Gtin', $gtin);
    }

    /**
     * Region test
     *
     * @covers Gtin::getPrefixRegion
     */
    public function testRegion(): void
    {
        $gtin = new Gtin('78935761');
        $this->assertEquals('GS1 Brasil', $gtin->region);

        $region = Gtin::check('78935761')->region;
        $this->assertEquals('GS1 Brasil', $region);
    }

    /**
     * Check digit test
     *
     * @covers Gtin::getCheckDigit
     */
    public function testCheckDigit(): void
    {
        $gtin = new Gtin('78935761');
        $this->assertEquals(1, $gtin->checkDigit);

        $dv = Gtin::check('78935761')->checkDigit;
        $this->assertEquals(1, $dv);
    }

    /**
     * Prefix test
     *
     * @covers Gtin::getPrefix
     * @covers Gtin::getType
     */
    public function testPrefix(): void
    {
        $gtin = new Gtin('78935761');
        $this->assertEquals('789', $gtin->prefix);
    }

    /**
     * Gtin is Valid
     *
     * @covers Gtin::isValid
     */
    public function testIsValid(): void
    {
        $gtin = new Gtin('78935761');
        $this->assertTrue($gtin->isValid());
    }

    /**
     * SEM GTIN is Valid
     *
     * @covers Gtin::isValid
     */
    public function testSemGetin(): void
    {
        $gtin = new Gtin('SEM GTIN');
        $this->assertTrue($gtin->isValid());
    }

    /**
     * Test empty Gtin number
     */
    public function testFailStringEmptyGtin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Um numero GTIN deve ser passado.');

        new Gtin('');
    }

    /**
     * Gtin null test
     */
    public function testFailNullGtin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Um numero GTIN deve ser passado.');

        new Gtin(null);
    }

    /**
     * Invalid string for GTIN test
     */
    public function testInvalidStringGtin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Um numero GTIN contêm apenas numeros [A12345] não é aceito.'
        );

        new Gtin('A12345');
    }

    /**
     * Minimum length test
     */
    public function testInvalidMinLengthGtin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Apenas numeros GTIN 8, 12, 13 ou 14 este ' .
                '[12345] não atende esses parâmetros.'
        );

        new Gtin('12345');
    }

    /**
     * Maximum length test
     */
    public function testInvalidMaxLengthGtin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Apenas numeros GTIN 8, 12, 13 ou 14 este [1234567890123456]' .
                ' não atende esses parâmetros.'
        );

        new Gtin('1234567890123456');
    }

    /**
     * Invalid length test
     */
    public function testInvalidLengthGtin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Apenas numeros GTIN 8, 12, 13 ou 14 este [1234567890]' .
                ' não atende esses parâmetros.'
        );

        new Gtin('1234567890');
    }

    /**
     * Invalid prefix test
     */
    public function testInvalidPrefixGtin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'O prefixo 510 do GTIN é INVALIDO [Not Found].'
        );

        Gtin::check('5109907267612')->isValid();
    }

    /**
     * Invalid prefix test
     *
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Somente prefixos do Brasil [789 e 790]
     *   são aceitáveis. O prefixo 779 do GTIN é INVALIDO [GS1 Argentina]..
     */
    /*
    public function testInvalidPrefixNonBrasil(): void
    {
        $gtin = Gtin::check('77935762')->isValid();
    }*/

    /**
     * Invalid check digit test
     */
    public function testInvalidCheckDigit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'GTIN [7890142547851] o digito verificador é INVALIDO.'
        );

        Gtin::check('7890142547851')->isValid();
    }

    /**
     * Gtin14 first digit test
     */
    public function testFailGtin14BeginsZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Um GTIN 14 não pode iniciar com numeral ZERO.'
        );

        Gtin::check('07890142547852')->isValid();
    }
}
