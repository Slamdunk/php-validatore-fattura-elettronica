<?php

declare(strict_types=1);

namespace SlamFatturaElettronica\Tests;

use PHPUnit\Framework\TestCase;
use SlamFatturaElettronica\Exception\InvalidXmlStructureException;
use SlamFatturaElettronica\Exception\InvalidXsdStructureComplianceException;
use SlamFatturaElettronica\Validator;

/**
 * @covers \SlamFatturaElettronica\Validator
 */
final class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    private $validator;

    protected function setUp()
    {
        $this->validator = new Validator();
    }

    /**
     * @dataProvider getValidXmls
     */
    public function testAssertValidXml(string $filename): void
    {
        $xml = $this->getXmlContent($filename);

        $this->validator->assertValidXml($xml);

        static::assertTrue(true);
    }

    public function getValidXmls(): array
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
        ];
    }

    public function testAssertValidXmlWithType(): void
    {
        static::markTestIncomplete('Missing valid example for Fattura Semplificata');

        $xml = $this->getXmlContent('ok_semplificata_IT01234567890.xml');

        $this->validator->assertValidXml($xml, Validator::XSD_FATTURA_SEMPLIFICATA_1_0);

        static::assertTrue(true);
    }

    public function testAssertInvalidXml(): void
    {
        $xml = $this->getXmlContent('invalid_xml_tags.xml');

        static::expectException(InvalidXmlStructureException::class);

        $this->validator->assertValidXml($xml);
    }

    public function testAssertInvalidXsdStructureCompliance(): void
    {
        $xml = $this->getXmlContent('invalid_xsd_structure_compliance.xml');

        static::expectException(InvalidXsdStructureComplianceException::class);

        $this->validator->assertValidXml($xml);
    }

    private function getXmlContent(string $filename): string
    {
        return \file_get_contents(__DIR__ . '/TestAsset/' . $filename);
    }
}
