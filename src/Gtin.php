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

namespace NFePHP\Gtin;

/**
 * Class for validation of GTIN numbers used in NFe
 * 
 * @category  Library
 * @package   NFePHP\Gtin
 * @author    Roberto L. Machado <liuux.rlm@gmail.com>
 * @copyright 2021 NFePHP Copyright (c) 
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      http://github.com/nfephp-org/sped-gtin
 */
final class Gtin
{
    /**
     * Prefix of GTIN
     * 
     * @var string
     */
    public $prefix;
    /**
     * Region Name
     * 
     * @var string
     */
    public $region;
    /**
     * Check digit
     * 
     * @var integer
     */
    public $checkDigit;
    /**
     * Type of GTIN
     * 
     * @var integer
     */
    public $type;
    /**
     * Number
     * 
     * @var string
     */
    protected $number;
    /**
     * Length of GTIN
     * 
     * @var integer
     */
    protected $lenght;
    /**
     * Prefixes collection
     * 
     * @var array
     */
    protected $stdPrefixCollection;
    /**
     * Validation of prefix of GTIN
     * 
     * @var boolean
     */
    protected $validPrefix = false;
    /**
     * Indication SEM GTIN
     * 
     * @var boolean 
     */
    protected $semgtin = false;

    /**
     * Caonstructor
     * 
     * @param string|null $gtin gtin number
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct(string $gtin = null)
    {
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
     * 
     * @return \NFePHP\Gtin\Gtin
     */
    public static function check(string $gtin = null)
    {
        return new static($gtin);
    }

    /**
     * Validate GTIN 8, 12, 13, or 14 with check digit
     *
     * @return bool
     * 
     * @throws \InvalidArgumentException
     */
    public function isValid()
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
     * Extract region prefix
     *
     * @return string
     */
    protected function getPrefix()
    {
        $type = $this->getType();
        switch ($type) {
        case 14: //begins with number not zero
            return substr($this->number, 1, 3);
        default:
            return substr($this->number, 0, 3);
        }
    }

    /**
     * Identify GTIN type GTIN 8,12,13,14 or NONE
     *
     * @return int
     */
    protected function getType()
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
     *
     * @return boolean
     */
    protected function isPrefixValid()
    {
        return $this->validPrefix;
    }

    /**
     * Recover region from prefix code
     *
     * @param string $prefix region prefix
     * 
     * @return string
     */
    protected function getPrefixRegion($prefix)
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
     *
     * @return integer
     */
    protected function getCheckDigit()
    {
        $len = (int) strlen($this->number);
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
