<?php

namespace NFePHP\Gtin;

class PrefixDefinition
{

    private string $nIni;
    private string $nFim;
    private string $restricted;
    private string $region;
    public function __construct(string $nIni, string $nFim, string $restricted, string $region)
    {
        $this->nIni = $nIni;
        $this->nFim = $nFim;
        $this->restricted = $restricted;
        $this->region = $region;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function hasPrefix(string $prefix): bool
    {
        $pf = (int) $prefix;
        $nI = (int) $this->nIni;
        $nF = (int) $this->nFim;
        return $pf >= $nI && $pf <= $nF;
    }

    public function isRestricted(): bool
    {
        return $this->restricted == '1';
    }

}
