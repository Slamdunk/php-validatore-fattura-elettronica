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
    private Validator $validator;

    protected function setUp(): void
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

        self::assertTrue(true);
    }

    /**
     * @return string[][]
     */
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

        $this->validator->assertValidXml($xml);
    }

    public function testAssertInvalidXsdStructureCompliance(): void
    {
        $xml = $this->getXmlContent('invalid_xsd_structure_compliance.xml');

        $this->expectException(InvalidXsdStructureComplianceException::class);

        $this->validator->assertValidXml($xml);
    }

    private function getXmlContent(string $filename): string
    {
        $content = \file_get_contents(__DIR__ . '/TestAsset/' . $filename);
        self::assertNotFalse($content);

        return $content;
    }

    public function testAssertValidNotice(): void
    {
        $xml = $this->getXmlContent('ok_IT01234567890_11111_EC_001.xml');

        $this->validator->assertValidXml($xml, Validator::XSD_MESSAGGI_LATEST);

        self::assertTrue(true);
    }

    public function testAssertInvalidNotice(): void
    {
        $xml = $this->getXmlContent('invalid_IT01234567890_11111_EC_001.xml');

        $this->expectException(InvalidXsdStructureComplianceException::class);

        $this->validator->assertValidXml($xml, Validator::XSD_MESSAGGI_LATEST);
    }
}
