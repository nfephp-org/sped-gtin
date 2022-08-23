<?php

namespace NFePHP\Gtin;

class PrefixDefinition
{
    private \stdClass $std;

    public function __construct(\stdClass $std)
    {
        $this->std = $std;
    }

    public function getRegion(): string
    {
        return (string)$this->std->region;
    }

    public function hasPrefix(string $prefix): bool
    {
        $pf = (int) $prefix;
        $nI = (int) $this->std->nIni;
        $nF = (int) $this->std->nFim;
        return $pf >= $nI && $pf <= $nF;
    }

    public function isRestricted(): bool
    {
        return $this->std->restricted == "1";
    }

}
