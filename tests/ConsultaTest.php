<?php

use NFePHP\Gtin\Common\Consulta;
use PHPUnit\Framework\TestCase;

class ConsultaTest extends TestCase
{
    /**
     * Consulta::getCodeMessage() reads src/Common/httpcodes.json, which was
     * never committed to this repository. Without the file every HTTP failure
     * (status != 200) emits a file_get_contents warning and the message falls
     * back to "Erro desconhecido.", hiding the real status code.
     */
    public function testHttpCodesFileExistsAndCodeMessageIsReturned()
    {
        $file = __DIR__ . '/../src/Common/httpcodes.json';
        $this->assertFileExists($file);
        $codes = json_decode(file_get_contents($file), true);
        $this->assertIsArray($codes);
        $this->assertArrayHasKey('500', $codes);

        $rc = new ReflectionClass(Consulta::class);
        $consulta = $rc->newInstanceWithoutConstructor();
        $method = $rc->getMethod('getCodeMessage');
        $method->setAccessible(true);
        $this->assertStringContainsStringIgnoringCase('erro interno', $method->invoke($consulta, 500));
        $this->assertEquals('Erro desconhecido.', $method->invoke($consulta, 99999));
    }
}
