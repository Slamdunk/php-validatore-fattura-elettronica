<?php

declare(strict_types=1);

namespace SlamFatturaElettronica\Exception;

use ErrorException;

final class InvalidXmlStructureException extends ErrorException implements ExceptionInterface {}
