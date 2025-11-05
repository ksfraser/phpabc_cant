<?php

namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use PHPUnit\Framework\TestCase;

class StaffWidthParserTest extends TestCase
{
    private StaffWidthParser $parser;

    protected function setUp(): void
    {
        $this->parser = new StaffWidthParser();
    }

    public function testCanParseValidStaffWidthDirectives()
    {
        $this->assertTrue($this->parser->canParse('%%staffwidth 800'));
        $this->assertTrue($this->parser->canParse('%%staffwidth 600pt'));
        $this->assertTrue($this->parser->canParse('%%staffwidth 15cm'));
        $this->assertTrue($this->parser->canParse('%%staffwidth 12in'));
    }

    public function testCannotParseInvalidStaffWidthDirectives()
    {
        $this->assertFalse($this->parser->canParse('%%staffwidth'));
        $this->assertFalse($this->parser->canParse('%%pagewidth 8.5in'));
        $this->assertFalse($this->parser->canParse('%%scale 0.8'));
    }

    public function testParseStaffWidthDirectiveWithUnits()
    {
        $result = $this->parser->parse('%%staffwidth 600pt');
        $this->assertEquals('staffwidth', $result['directive']);
        $this->assertEquals('600pt', $result['value']);
        $this->assertEquals('600', $result['dimension']);
        $this->assertEquals('pt', $result['unit']);
    }

    public function testParseStaffWidthDirectiveWithoutUnits()
    {
        $result = $this->parser->parse('%%staffwidth 800');
        $this->assertEquals('staffwidth', $result['directive']);
        $this->assertEquals('800', $result['value']);
        $this->assertEquals('800', $result['dimension']);
        $this->assertEquals('', $result['unit']); // no unit specified
    }

    public function testParseStaffWidthDirectiveWithCm()
    {
        $result = $this->parser->parse('%%staffwidth 15cm');
        $this->assertEquals('staffwidth', $result['directive']);
        $this->assertEquals('15cm', $result['value']);
        $this->assertEquals('15', $result['dimension']);
        $this->assertEquals('cm', $result['unit']);
    }

    public function testValidateValidStaffWidthDirective()
    {
        $this->assertTrue($this->parser->validate('%%staffwidth 800'));
        $this->assertTrue($this->parser->validate('%%staffwidth 600pt'));
        $this->assertTrue($this->parser->validate('%%staffwidth 15cm'));
        $this->assertTrue($this->parser->validate('%%staffwidth 12in'));
        $this->assertTrue($this->parser->validate('%%staffwidth 500mm'));
        $this->assertTrue($this->parser->validate('%%staffwidth 1200px'));
    }

    public function testValidateInvalidStaffWidthDirective()
    {
        $this->assertFalse($this->parser->validate('%%staffwidth'));
        $this->assertFalse($this->parser->validate('%%staffwidth invalid'));
        $this->assertFalse($this->parser->validate('%%staffwidth 0')); // zero not allowed
        $this->assertFalse($this->parser->validate('%%staffwidth -100')); // negative not allowed
    }
}