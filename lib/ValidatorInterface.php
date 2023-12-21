<?php

declare(strict_types=1);

namespace SlamFatturaElettronica;

use SlamFatturaElettronica\Exception\ExceptionInterface;

interface ValidatorInterface
{
    /**
     * @param non-empty-string $xml
     *
     * @throws ExceptionInterface
     */
    public function assertValidXml(string $xml): void;
}
