<?php

/**
 * This file belongs to the NFePHP project
 * php version 7.0 or higher
 *
 * @category  Library
 * @package   NFePHP\Gtin
 * @author    Roberto L. Machado <liuux.rlm@gmail.com>
 * @copyright 2021 NFePHP Copyright (c) 
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      http://github.com/nfephp-org/sped-gtin
 */

namespace NFePHP\Gtin\Tests;

use NFePHP\Gtin\Gtin;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test Class
 * 
 * @category  Library
 * @package   NFePHP\Gtin
 * @author    Roberto L. Machado <liuux.rlm@gmail.com>
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
     * 
     * @return void
     */
    public function testCanInstantiate()
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
     * 
     * @return void
     */
    public function testCanInstantiateStatic()
    {
        $gtin = Gtin::check('78935761');
        $this->assertInstanceOf('NFePHP\Gtin\Gtin', $gtin);
    }

    /**
     * Region test
     * 
     * @covers Gtin::getPrefixRegion
     * 
     * @return void
     */
    public function testRegion()
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
     * 
     * @return void
     */
    public function testCheckDigit()
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
     * 
     * @return void
     */
    public function testPrefix()
    {
        $gtin = new Gtin('78935761');
        $this->assertEquals('789', $gtin->prefix);
    }
    
    /**
     * Gtin is Valid
     * 
     * @covers Gtin::isValid
     * 
     * @return void
     */
    public function testIsValid()
    {
        $gtin = new Gtin('78935761');
        $this->assertTrue($gtin->isValid());
    }
    
    /**
     * SEM GTIN is Valid
     * 
     * @covers Gtin::isValid
     * 
     * @return void
     */
    public function testSemGetin()
    {
        $gtin = new Gtin('SEM GTIN');
        $this->assertTrue($gtin->isValid());
    }
    
    /**
     * Test empty Gtin number
     * 
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Um numero GTIN deve ser passado.
     * 
     * @return void
     */
    public function testFailStringEmptyGtin()
    {
        $gtin = new Gtin('');
    }
    
    /**
     * Gtin null test
     * 
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Um numero GTIN deve ser passado.
     * 
     * @return void
     */    
    public function testFailNullGtin()
    {
        $gtin = new Gtin(null);
    }
    
    /**
     * Invalid string for GTIN test
     * 
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Um numero GTIN contêm apenas numeros 
     *    [A12345] não é aceito.
     * 
     * @return void
     */
    public function testInvalidStringGtin()
    {
        $gtin = new Gtin('A12345');
    }
    
    /**
     * Minimum length test
     * 
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Apenas numeros GTIN 8, 12, 13 ou 14 
     *    este [12345] não atende esses parâmetros.
     * 
     * @return void
     */
    public function testInvalidMinLengthGtin()
    {
        $gtin = new Gtin('12345');
    }

    /**
     * Maximum length test
     * 
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Apenas numeros GTIN 8, 12, 13 ou 14 
     *   este [1234567890123456] não atende esses parâmetros.
     * 
     * @return void
     */
    public function testInvalidMaxLengthGtin()
    {
        $gtin = new Gtin('1234567890123456');
    }    
    
    /**
     * Invalid length test
     * 
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Apenas numeros GTIN 8, 12, 13 ou 14 
     *    este [1234567890] não atende esses parâmetros.
     * 
     * @return void
     */
    public function testInvalidLengthGtin()
    {
        $gtin = new Gtin('1234567890');
    }
    
    /**
     * Invalid prefix test
     * 
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage O prefixo 510 do GTIN é INVALIDO [Not Found].
     * 
     * @return void
     */
    public function testInvalidPrefixGtin()
    {
        $gtin = Gtin::check('5109907267612')->isValid();
    }
    
    /**
     * Invalid prefix test
     * 
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Somente prefixos do Brasil [789 e 790] 
     *   são aceitáveis. O prefixo 779 do GTIN é INVALIDO [GS1 Argentina]..
     * 
     * @return void
     */
    /*
    public function testInvalidPrefixNonBrasil()
    {
        $gtin = Gtin::check('77935762')->isValid();
    }*/
    
    /**
     * Invalid check digit test
     * 
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage GTIN [7890142547851] o digito verificador 
     *    é INVALIDO.
     * 
     * @return void
     */
    public function testInvalidCheckDigit()
    {
        $resp = Gtin::check('7890142547851')->isValid();
    }
    
    /**
     * Gtin14 first digit test
     * 
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Um GTIN 14 não pode iniciar com numeral ZERO.
     * 
     * @return void
     */
    public function testFailGtin14BeginsZero()
    {
        $resp = Gtin::check('07890142547852')->isValid();
    }
}