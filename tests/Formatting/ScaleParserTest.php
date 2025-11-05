<?php

namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use PHPUnit\Framework\TestCase;

class ScaleParserTest extends TestCase
{
    private ScaleParser $parser;

    protected function setUp(): void
    {
        $this->parser = new ScaleParser();
    }

    public function testCanParseValidScaleDirectives()
    {
        $this->assertTrue($this->parser->canParse('%%scale 1.0'));
        $this->assertTrue($this->parser->canParse('%%scale 0.8'));
        $this->assertTrue($this->parser->canParse('%%scale 1.2'));
        $this->assertTrue($this->parser->canParse('%%scale 0.5'));
    }

    public function testCannotParseInvalidScaleDirectives()
    {
        $this->assertFalse($this->parser->canParse('%%scale'));
        $this->assertFalse($this->parser->canParse('%%scale invalid'));
        $this->assertFalse($this->parser->canParse('%%pagewidth 8.5in'));
        $this->assertFalse($this->parser->canParse('%%scale 6.0')); // out of range
    }

    public function testParseValidScaleDirective()
    {
        $result = $this->parser->parse('%%scale 0.8');
        $this->assertEquals('scale', $result['directive']);
        $this->assertEquals('0.8', $result['value']);
        $this->assertEquals(0.8, $result['scale_value']);
    }

    public function testParseScaleDirectiveWithDifferentValues()
    {
        $result = $this->parser->parse('%%scale 1.5');
        $this->assertEquals('scale', $result['directive']);
        $this->assertEquals('1.5', $result['value']);
        $this->assertEquals(1.5, $result['scale_value']);
    }

    public function testValidateValidScaleDirective()
    {
        $this->assertTrue($this->parser->validate('%%scale 1.0'));
        $this->assertTrue($this->parser->validate('%%scale 0.1'));
        $this->assertTrue($this->parser->validate('%%scale 5.0'));
        $this->assertTrue($this->parser->validate('%%scale 2.5'));
    }

    public function testValidateInvalidScaleDirective()
    {
        $this->assertFalse($this->parser->validate('%%scale'));
        $this->assertFalse($this->parser->validate('%%scale invalid'));
        $this->assertFalse($this->parser->validate('%%scale 0.05')); // below minimum
        $this->assertFalse($this->parser->validate('%%scale 5.1')); // above maximum
        $this->assertFalse($this->parser->validate('%%scale -1.0')); // negative
    }
}