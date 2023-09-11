<?php

declare(strict_types=1);

namespace SlamFatturaElettronica\Exception;

use ErrorException;

final class InvalidXsdStructureComplianceException extends ErrorException implements ExceptionInterface {}
