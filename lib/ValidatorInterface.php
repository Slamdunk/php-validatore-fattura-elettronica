<?php

declare(strict_types=1);

namespace SlamFatturaElettronica;

interface ValidatorInterface
{
    public function assertValidXml(string $xml): void;
}
