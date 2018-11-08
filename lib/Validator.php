<?php

declare(strict_types=1);

namespace SlamFatturaElettronica;

use DOMDocument;

final class Validator implements ValidatorInterface
{
    const XSD_FATTURA_ORDINARIA_1_2         = 'Schema_VFPR12.xsd';
    const XSD_FATTURA_ORDINARIA_LATEST      = 'Schema_VFPR12.xsd';

    const XSD_FATTURA_SEMPLIFICATA_1_0      = 'Schema_VFSM10.xsd';
    const XSD_FATTURA_SEMPLIFICATA_LATEST   = 'Schema_VFSM10.xsd';

    private $xsdCache = [];

    /**
     * @throws Exception\InvalidXmlStructureException
     * @throws Exception\InvalidXsdStructureComplianceException
     */
    public function assertValidXml(string $xml, string $type = self::XSD_FATTURA_ORDINARIA_LATEST): void
    {
        $dom = new DOMDocument($xml);

        \set_error_handler(function ($errno, $errstr = '', $errfile = '', $errline = 0) {
            throw new Exception\InvalidXmlStructureException($errstr, $errno, $errno, $errfile, $errline);
        });
        $dom->loadXML($xml);
        \restore_error_handler();

        $xsd = $this->getXsd($type);

        \set_error_handler(function ($errno, $errstr = '', $errfile = '', $errline = 0) {
            throw new Exception\InvalidXsdStructureComplianceException($errstr, $errno, $errno, $errfile, $errline);
        });
        $dom->schemaValidateSource($xsd);
        \restore_error_handler();
    }

    private function getXsd(string $type): string
    {
        if (! isset($this->xsdCache[$type])) {
            $xsdFilename = \dirname(__DIR__) . '/xsd/' . $type;

            /** @var string $xsd */
            $xsd = \file_get_contents($xsdFilename);

            // Let's get rid of external HTTP call
            $xmldsigFilename       = \dirname(__DIR__) . '/xsd/xmldsig-core-schema.xsd';
            $this->xsdCache[$type] = \preg_replace('/(\bschemaLocation=")[^"]+"/', \sprintf('\1%s"', $xmldsigFilename), $xsd);
        }

        return $this->xsdCache[$type];
    }
}
