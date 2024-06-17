<?php

declare(strict_types=1);

namespace SlamFatturaElettronica\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SlamFatturaElettronica\Exception\ExceptionInterface;
use SlamFatturaElettronica\Exception\InvalidXmlStructureException;
use SlamFatturaElettronica\Exception\InvalidXsdStructureComplianceException;
use SlamFatturaElettronica\Validator;

/**
 * @internal
 */
#[CoversClass(Validator::class)]
final class ValidatorTest extends TestCase
{
    /** @param non-empty-string $filename */
    #[DataProvider('getValidXmls')]
    public function testAssertValidXml(string $filename): void
    {
        $xml = $this->getXmlContent($filename);

        (new Validator())->assertValidXml($xml);
    }

    /** @return list<list<non-empty-string>> */
    public static function getValidXmls(): array
    {
        return [
            ['ok_IT01234567890_FPA01.xml'],
            ['ok_IT01234567890_FPA02.xml'],
            ['ok_IT01234567890_FPA03.xml'],
            ['ok_IT01234567890_FPR01.xml'],
            ['ok_IT01234567890_FPR02.xml'],
            ['ok_IT01234567890_FPR03.xml'],
            ['ok_ITHVQWPH73P42H501Y_00023.xml'],
            ['ok_ITHVQWPH73P42H501Y_X0024.xml'],
            ['ok_bug_attribute_with_space.xml'],
        ];
    }

    public function testAssertValidXmlWithType(): void
    {
        self::markTestIncomplete('Missing valid example for Fattura Semplificata');

        /*
        $xml = $this->getXmlContent('ok_semplificata_IT01234567890.xml');
        $this->validator->assertValidXml($xml, Validator::XSD_FATTURA_SEMPLIFICATA_1_0);
        self::assertTrue(true);
         */
    }

    public function testAssertInvalidXml(): void
    {
        $xml = $this->getXmlContent('invalid_xml_tags.xml');

        $this->expectException(InvalidXmlStructureException::class);
        $this->expectExceptionMessage('DOMDocument::loadXML(): Namespace prefix ns2 on FatturaElettronica is not defined in Entity, line: 1');

        (new Validator())->assertValidXml($xml);
    }

    public function testAssertInvalidXsdStructureCompliance(): void
    {
        $xml = $this->getXmlContent('invalid_xsd_structure_compliance.xml');

        $this->expectException(InvalidXsdStructureComplianceException::class);
        $this->expectExceptionMessage('DOMDocument::schemaValidateSource(): Element \'IdTrasmittente\': Missing child element(s). Expected is ( IdCodice ).');

        (new Validator())->assertValidXml($xml);
    }

    public function testAssertValidNotice(): void
    {
        $xml = $this->getXmlContent('ok_IT01234567890_11111_EC_001.xml');

        (new Validator())->assertValidXml($xml, Validator::XSD_MESSAGGI_LATEST);
    }

    public function testAssertInvalidNotice(): void
    {
        $xml = $this->getXmlContent('invalid_IT01234567890_11111_EC_001.xml');

        $this->expectException(InvalidXsdStructureComplianceException::class);

        (new Validator())->assertValidXml($xml, Validator::XSD_MESSAGGI_LATEST);
    }

    /**
     * @param non-empty-string $filename
     *
     * @return non-empty-string
     */
    private function getXmlContent(string $filename): string
    {
        $content = \file_get_contents(__DIR__ . '/TestAsset/' . $filename);
        self::assertNotFalse($content);
        self::assertNotEmpty($content);

        return $content;
    }

    public function testGetAllErrorsXmlNotEmpty(): void
    {
        $xml        = $this->getXmlContent('invalid_xml_tags.xml');
        $exceptions = (new Validator())->getAllExceptions($xml);
        $exceptions = \array_map(static function (ExceptionInterface $exception): array {
            return [$exception::class, $exception->getMessage()];
        }, $exceptions);

        self::assertSame([
            [InvalidXmlStructureException::class, 'DOMDocument::loadXML(): Namespace prefix ns2 on FatturaElettronica is not defined in Entity, line: 1'],
            [InvalidXmlStructureException::class, 'DOMDocument::loadXML(): Couldn\'t find end of Start Tag FatturaElettronica line 1 in Entity, line: 1'],
            [InvalidXsdStructureComplianceException::class, 'DOMDocument::schemaValidateSource(): Element \'ns2:FatturaElettronica\': No matching global declaration available for the validation root.'],
        ], $exceptions);
    }

    public function testGetAllErrorsXsdNotEmpty(): void
    {
        $xml        = $this->getXmlContent('invalid_xsd_content.xml');
        $exceptions = (new Validator())->getAllExceptions($xml);
        $exceptions = \array_map(static function (ExceptionInterface $exception): array {
            return [$exception::class, $exception->getMessage()];
        }, $exceptions);

        self::assertSame([
            [InvalidXsdStructureComplianceException::class, 'DOMDocument::schemaValidateSource(): Element \'IdPaese\': [facet \'pattern\'] The value \'ITALIA\' is not accepted by the pattern \'[A-Z]{2}\'.'],
            [InvalidXsdStructureComplianceException::class, 'DOMDocument::schemaValidateSource(): Element \'ImponibileImporto\': [facet \'pattern\'] The value \'5\' is not accepted by the pattern \'[\-]?[0-9]{1,11}\.[0-9]{2}\'.'],
            [InvalidXsdStructureComplianceException::class, 'DOMDocument::schemaValidateSource(): Element \'DataScadenzaPagamento\': \'2030\' is not a valid value of the atomic type \'xs:date\'.'],
        ], $exceptions);
    }

    public function testGetAllErrorsIsEmpty(): void
    {
        $xml    = $this->getXmlContent('ok_IT01234567890_FPR01.xml');
        $errors = (new Validator())->getAllExceptions($xml);

        self::assertSame([], $errors);
    }
}
