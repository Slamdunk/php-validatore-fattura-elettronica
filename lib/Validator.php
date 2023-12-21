<?php

declare(strict_types=1);

namespace SlamFatturaElettronica;

use DOMDocument;
use SlamFatturaElettronica\Exception\ExceptionInterface;

final class Validator implements ValidatorInterface
{
    public const XSD_FATTURA_ORDINARIA_1_2    = 'Schema_VFPR12.xsd';
    public const XSD_FATTURA_ORDINARIA_1_2_1  = 'Schema_VFPR121a.xsd';
    public const XSD_FATTURA_ORDINARIA_LATEST = 'Schema_VFPR121a.xsd';

    public const XSD_FATTURA_SEMPLIFICATA_1_0    = 'Schema_VFSM10.xsd';
    public const XSD_FATTURA_SEMPLIFICATA_LATEST = 'Schema_VFSM10.xsd';

    public const XSD_MESSAGGI_1_0    = 'MessaggiFatturaTypes_v1.0.xsd';
    public const XSD_MESSAGGI_1_1    = 'MessaggiTypes_v1.1.xsd';
    public const XSD_MESSAGGI_LATEST = 'MessaggiTypes_v1.1.xsd';

    /** @var array<non-empty-string, non-empty-string> */
    private array $xsdCache = [];

    /**
     * @param non-empty-string $xml
     * @param non-empty-string $type
     *
     * @throws ExceptionInterface
     */
    public function assertValidXml(string $xml, string $type = self::XSD_FATTURA_ORDINARIA_LATEST): void
    {
        $exceptions = $this->getAllExceptions($xml, $type);

        if ([] === $exceptions) {
            return;
        }

        throw $exceptions[0];
    }

    /**
     * @param non-empty-string $xml
     * @param non-empty-string $type
     *
     * @return list<ExceptionInterface>
     */
    public function getAllExceptions(string $xml, string $type = self::XSD_FATTURA_ORDINARIA_LATEST): array
    {
        $dom          = new DOMDocument();
        $dom->recover = true;
        $dom->loadXML($xml, \LIBXML_NOERROR);
        $xsd = $this->getXsd($type);

        $xsdExceptions = [];
        \set_error_handler(static function (int $errno, string $errstr = '', string $errfile = '', int $errline = 0) use (& $xsdExceptions): bool {
            $xsdExceptions[] = new Exception\InvalidXsdStructureComplianceException($errstr, $errno, $errno, $errfile, $errline);

            return true;
        });
        $dom->schemaValidateSource($xsd);
        \restore_error_handler();

        if ([] === $xsdExceptions) {
            return [];
        }

        $dom           = new DOMDocument();
        $xmlExceptions = [];
        \set_error_handler(static function (int $errno, string $errstr = '', string $errfile = '', int $errline = 0) use (& $xmlExceptions): bool {
            $xmlExceptions[] = new Exception\InvalidXmlStructureException($errstr, $errno, $errno, $errfile, $errline);

            return true;
        });
        $dom->loadXML($xml);
        \restore_error_handler();

        return \array_merge($xmlExceptions, $xsdExceptions);
    }

    /**
     * @param non-empty-string $type
     *
     * @return non-empty-string
     */
    private function getXsd(string $type): string
    {
        if (! isset($this->xsdCache[$type])) {
            $xsdFilename = \dirname(__DIR__) . '/xsd/' . $type;

            $xsd = \file_get_contents($xsdFilename);
            \assert(false !== $xsd);

            // Let's get rid of external HTTP call
            $xmldsigFilename = \dirname(__DIR__) . '/xsd/xmldsig-core-schema.xsd';
            $xsdLocal        = \preg_replace('/(\bschemaLocation=")[^"]+"/', \sprintf('\1%s"', $xmldsigFilename), $xsd);
            \assert(null !== $xsdLocal && '' !== $xsdLocal);
            $this->xsdCache[$type] = $xsdLocal;
        }

        return $this->xsdCache[$type];
    }
}
