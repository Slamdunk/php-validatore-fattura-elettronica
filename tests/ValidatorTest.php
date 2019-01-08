<?php

namespace SlamFatturaElettronica\Tests;

use PHPUnit_Framework_TestCase;
use SlamFatturaElettronica\Validator;

/**
 * @covers \SlamFatturaElettronica\Validator
 */
final class ValidatorTest extends PHPUnit_Framework_TestCase
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
    public function testAssertValidXml($filename)
    {
        $xml = $this->getXmlContent($filename);

        $this->validator->assertValidXml($xml);

        static::assertTrue(true);
    }

    public function getValidXmls()
    {
        return array(
            array('ok_IT01234567890_FPA01.xml'),
            array('ok_IT01234567890_FPA02.xml'),
            array('ok_IT01234567890_FPA03.xml'),
            array('ok_IT01234567890_FPR01.xml'),
            array('ok_IT01234567890_FPR02.xml'),
            array('ok_IT01234567890_FPR03.xml'),
            array('ok_ITHVQWPH73P42H501Y_00023.xml'),
            array('ok_ITHVQWPH73P42H501Y_X0024.xml'),
        );
    }

    public function testAssertValidXmlWithType()
    {
        static::markTestIncomplete('Missing valid example for Fattura Semplificata');

        $xml = $this->getXmlContent('ok_semplificata_IT01234567890.xml');

        $this->validator->assertValidXml($xml, Validator::XSD_FATTURA_SEMPLIFICATA_1_0);

        static::assertTrue(true);
    }

    public function testAssertInvalidXml()
    {
        $xml = $this->getXmlContent('invalid_xml_tags.xml');

        static::setExpectedException('SlamFatturaElettronica\\Exception\\InvalidXmlStructureException');

        $this->validator->assertValidXml($xml);
    }

    public function testAssertInvalidXsdStructureCompliance()
    {
        $xml = $this->getXmlContent('invalid_xsd_structure_compliance.xml');

        static::setExpectedException('SlamFatturaElettronica\\Exception\\InvalidXsdStructureComplianceException');

        $this->validator->assertValidXml($xml);
    }

    private function getXmlContent($filename)
    {
        return \file_get_contents(__DIR__ . '/TestAsset/' . $filename);
    }

    public function testAssertValidNotice()
    {
        $xml = $this->getXmlContent('ok_IT01234567890_11111_EC_001.xml');

        $this->validator->assertValidXml($xml, Validator::XSD_MESSAGGI_LATEST);

        static::assertTrue(true);
    }

    public function testAssertInvalidNotice()
    {
        $xml = $this->getXmlContent('invalid_IT01234567890_11111_EC_001.xml');

        static::setExpectedException('SlamFatturaElettronica\\Exception\\InvalidXsdStructureComplianceException');

        $this->validator->assertValidXml($xml, Validator::XSD_MESSAGGI_LATEST);
    }
}
