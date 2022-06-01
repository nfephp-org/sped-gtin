<?php

namespace NFePHP\Gtin;

/**
 * Class for validation of GTIN numbers used in NFe
 *
 * @category  Library
 * @package   NFePHP\Gtin
 * @author    Roberto L. Machado <linux dot rlm at gmail dot com>
 * @copyright 2021 NFePHP Copyright (c)
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      http://github.com/nfephp-org/sped-gtin
 */

use NFePHP\Common\Certificate;
use NFePHP\Gtin\Common\Consulta;

final class Gtin
{
    /**
     * Prefix of GTIN
     */
    public string $prefix;
    /**
     * Region Name
     */
    public string $region;
    /**
     * Check digit
     */
    public int $checkDigit;
    /**
     * Type of GTIN
     */
    public int $type;
    /**
     * Number
     */
    protected string $number;
    /**
     * Length of GTIN
     */
    protected int $lenght;
    /**
     * Prefixes collection
     */
    protected array $stdPrefixCollection;
    /**
     * Validation of prefix of GTIN
     */
    protected bool $validPrefix = false;
    /**
     * Indication SEM GTIN
     */
    protected bool $semgtin = false;
    /**
     * @var Certificate|null
     */
    protected $certificate;
    /**
     * Config como do sped-nfe
     */
    protected \stdClass $config;

    /**
     * Caonstructor
     *
     * @param string|null $gtin gtin number
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $gtin = null, Certificate $certificate = null)
    {
        $this->certificate = $certificate;

        if ($gtin == 'SEM GTIN') {
            $this->number = 'SEM GTIN';
            $this->prefix = '000';
            $this->region = 'GS1 Brasil';
            $this->checkDigit = 0;
            $this->type = 0;
            $this->semgtin = true;
            $this->validPrefix = true;
            return;
        }
        $this->stdPrefixCollection = json_decode(
            file_get_contents(__DIR__.'/prefixcollection.json')
        );
        if (empty($gtin)) {
            throw new \InvalidArgumentException("Um numero GTIN deve ser passado.");
        }
        if (preg_match('/[^0-9]/', $gtin)) {
            throw new \InvalidArgumentException(
                "Um numero GTIN contêm apenas numeros [$gtin] não é aceito."
            );
        }
        $this->lenght = (int) strlen($gtin);
        if ($this->lenght != 8
            && $this->lenght != 12
            && $this->lenght != 13
            && $this->lenght != 14
        ) {
            throw new \InvalidArgumentException(
                "Apenas numeros GTIN 8, 12, 13 ou 14 este [$gtin] "
                . "não atende esses parâmetros."
            );
        }
        $this->number = $gtin;
        $this->prefix = $this->getPrefix();
        $this->region = $this->getPrefixRegion($this->prefix);
        $this->checkDigit = $this->getCheckDigit();
        $this->type = $this->getType();

    }

    /**
     * Static instantiation off class
     *
     * @param string|null $gtin gtin number
     */
    public static function check(string $gtin = null, Certificate $certificate = null): self
    {
        return new static($gtin, $certificate);
    }

    /**
     * Validate GTIN 8, 12, 13, or 14 with check digit
     *
     * @throws \InvalidArgumentException
     */
    public function isValid(): bool
    {
        if ($this->semgtin) {
            return true;
        }
        if ($this->lenght == 14 && substr($this->number, 0, 1) == '0') {
            //first digit of GTIN14 can not be zero
            throw new \InvalidArgumentException(
                "Um GTIN 14 não pode iniciar com numeral ZERO."
            );
        }
        if (!$this->isPrefixValid()) {
            throw new \InvalidArgumentException(
                "O prefixo $this->prefix do GTIN é INVALIDO [$this->region]."
            );
        }
        /*
         * REATIVAR Futuramente
        if ($this->region != 'GS1 Brasil') {
            throw new \InvalidArgumentException(
                "Somente prefixos do Brasil [789 e 790] são aceitáveis. "
                . "O prefixo $this->prefix do GTIN é INVALIDO [$this->region]."
            );
        }*/
        $dv = (int) substr($this->number, -1);
        if ($dv !== $this->checkDigit) {
            throw new \InvalidArgumentException(
                "GTIN [$this->number] o digito verificador é INVALIDO."
            );
        }
        return true;
    }

    /**
     * Consulta GTIN no CCG SEFAZ
     *
     * @return object
     */
    public function consulta()
    {
        if (empty($this->number)) {
            throw new \InvalidArgumentException(
                'A consulta não pode ser realizada sem indicar um numero GTIN.'
            );
        }
        if ($this->number == 'SEM GTIN') {
            throw new \InvalidArgumentException(
                'SEM GTIN não é um número a ser verificado'
            );
        }
        if (empty($this->certificate)) {
            throw new \InvalidArgumentException(
                'A consulta não pode ser realizada sem um Certificado digital'
            );
        }
        $this->isValid();
        $prefix = $this->getPrefix();
        if (!in_array($prefix, ['789', '790'])) {
            throw new \InvalidArgumentException(
                'Esse GTIN não pertence ao BRASIL e portanto não pode ser consultado no CCG'
            );
        }
        $cons = new Consulta($this->certificate);
        $cons->setEncriptPrivateKey(true);
        return $cons->consulta($this->number);
    }

    /**
     * Extract region prefix
     */
    protected function getPrefix(): string
    {
        $type = $this->getType();
        $g14 = str_pad($this->number, 14, '0', STR_PAD_LEFT);
        switch ($type) {
        case 8:
            return substr($g14, 6, 3);
        default:
            return substr($g14, 1, 3);
        }
    }

    /**
     * Identify GTIN type GTIN 8,12,13,14 or NONE
     */
    protected function getType(): int
    {
        $gtinnorm = str_pad($this->number, 14, '0', STR_PAD_LEFT);
        if (substr($gtinnorm, 0, 6) == '000000') {
            //GTIN 8
            return 8;
        } elseif (substr($gtinnorm, 0, 2) == '00') {
            //GTIN 12
            return 12;
        } elseif (substr($gtinnorm, 0, 1) == '0') {
            //GTIN 13
            return 13;
        } elseif (substr($gtinnorm, 0, 1) != '0') {
            //GTIN 14
            return 14;
        }
        return 0;
    }

    /**
     * Validate prefix region
     */
    protected function isPrefixValid(): bool
    {
        return $this->validPrefix;
    }

    /**
     * Recover region from prefix code
     *
     * @param string $prefix region prefix
     */
    protected function getPrefixRegion(string $prefix): string
    {
        $pf = (int) $prefix;
        foreach ($this->stdPrefixCollection as $std) {
            $nI = (int) $std->nIni;
            $nF = (int) $std->nFim;
            $this->validPrefix = true;
            $region = $std->region;
            if ($pf >= $nI && $pf <= $nF) {
                return $region;
            }
        }
        $this->validPrefix = false;
        return "Not Found";
    }

    /**
     * Calculate check digit from GTIN 8, 12, 13 or 14
     */
    protected function getCheckDigit(): int
    {
        $len = strlen($this->number);
        $gtin = substr($this->number, 0, $len-1);
        $gtin = str_pad($gtin, 15, '0', STR_PAD_LEFT);
        $total = 0;
        for ($pos = 0; $pos < 15; $pos++) {
            $val = (int) $gtin[$pos];
            $total += ((($pos + 1) % 2) * 2 + 1) * $val; //$gtin[$pos];
        }
        $dv = 10 - ($total % 10);
        if ($dv == 10) {
            $dv = 0;
        }
        return $dv;
    }
}
