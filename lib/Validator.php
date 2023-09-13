<?php

declare(strict_types=1);

namespace SlamFatturaElettronica;

use DOMDocument;

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

    /** @var array<string, string> */
    private array $xsdCache = [];

    /**
     * @throws Exception\InvalidXmlStructureException
     * @throws Exception\InvalidXsdStructureComplianceException
     */
    public function assertValidXml(string $xml, string $type = self::XSD_FATTURA_ORDINARIA_LATEST): void
    {
        $dom          = new DOMDocument();
        $dom->recover = true;
        $dom->loadXML($xml, \LIBXML_NOERROR);
        $xsd = $this->getXsd($type);

        $xsdErrorArguments = null;
        \set_error_handler(static function (int $errno, string $errstr = '', string $errfile = '', int $errline = 0) use (& $xsdErrorArguments): bool {
            $xsdErrorArguments = \func_get_args();

            return true;
        });
        $dom->schemaValidateSource($xsd);
        \restore_error_handler();

        if (null === $xsdErrorArguments) {
            return;
        }

        $dom               = new DOMDocument();
        $xmlErrorArguments = null;
        \set_error_handler(static function (int $errno, string $errstr = '', string $errfile = '', int $errline = 0) use (& $xmlErrorArguments): bool {
            $xmlErrorArguments = \func_get_args();

            return true;
        });
        $dom->loadXML($xml);
        \restore_error_handler();

        if (null !== $xmlErrorArguments) {
            throw new Exception\InvalidXmlStructureException($xmlErrorArguments[1], $xmlErrorArguments[0], $xmlErrorArguments[0], $xmlErrorArguments[2], $xmlErrorArguments[3]);
        }

        throw new Exception\InvalidXsdStructureComplianceException($xsdErrorArguments[1], $xsdErrorArguments[0], $xsdErrorArguments[0], $xsdErrorArguments[2], $xsdErrorArguments[3]);
    }

    /**
     * @return array <string, string> Array of errors. An empty array will be returned if there are no errors
     */
    public function getAllErrors(string $xml, string $type = self::XSD_FATTURA_ORDINARIA_LATEST): array
    {
        $dom = new DOMDocument();
        $dom->recover = true;
        $dom->loadXML($xml, LIBXML_NOERROR);
        $xsd = $this->getXsd($type);

        $errors = [];

        set_error_handler(static function (int $errno, string $errstr = '', string $errfile = '', int $errline = 0) use (&$errors): bool {
            $errors[] = $errstr;

            return true;
        });

        $result = $dom->schemaValidateSource($xsd);

        restore_error_handler();

        if (!$result && !empty($errors)) {
            return $errors;
        }

        return [];
    }

    private function getXsd(string $type): string
    {
        if (! isset($this->xsdCache[$type])) {
            $xsdFilename = \dirname(__DIR__) . '/xsd/' . $type;

            /** @var string $xsd */
            $xsd = \file_get_contents($xsdFilename);

            // Let's get rid of external HTTP call
            $xmldsigFilename = \dirname(__DIR__) . '/xsd/xmldsig-core-schema.xsd';
            $xsdLocal        = \preg_replace('/(\bschemaLocation=")[^"]+"/', \sprintf('\1%s"', $xmldsigFilename), $xsd);
            \assert(null !== $xsdLocal);
            $this->xsdCache[$type] = $xsdLocal;
        }

        return $this->xsdCache[$type];
    }
}
