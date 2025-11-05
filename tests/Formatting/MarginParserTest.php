<?php

namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use PHPUnit\Framework\TestCase;

class MarginParserTest extends TestCase
{
    private MarginParser $parser;

    protected function setUp(): void
    {
        $this->parser = new MarginParser();
    }

    public function testCanParseValidMarginDirectives()
    {
        $this->assertTrue($this->parser->canParse('%%leftmargin 1.5cm'));
        $this->assertTrue($this->parser->canParse('%%rightmargin 2.0in'));
        $this->assertTrue($this->parser->canParse('%%topmargin 1.0cm'));
        $this->assertTrue($this->parser->canParse('%%bottommargin 1.2cm'));
    }

    public function testCannotParseInvalidMarginDirectives()
    {
        $this->assertFalse($this->parser->canParse('%%leftmargin'));
        $this->assertFalse($this->parser->canParse('%%invalidmargin 1.5cm'));
        $this->assertFalse($this->parser->canParse('%%pagewidth 8.5in'));
        $this->assertFalse($this->parser->canParse('%%scale 0.8'));
    }

    public function testParseLeftMarginDirective()
    {
        $result = $this->parser->parse('%%leftmargin 1.5cm');
        $this->assertEquals('leftmargin', $result['directive']);
        $this->assertEquals('1.5cm', $result['value']);
        $this->assertEquals('left', $result['margin_type']);
        $this->assertEquals('1.5', $result['dimension']);
        $this->assertEquals('cm', $result['unit']);
    }

    public function testParseRightMarginDirective()
    {
        $result = $this->parser->parse('%%rightmargin 2.0in');
        $this->assertEquals('rightmargin', $result['directive']);
        $this->assertEquals('2.0in', $result['value']);
        $this->assertEquals('right', $result['margin_type']);
        $this->assertEquals('2.0', $result['dimension']);
        $this->assertEquals('in', $result['unit']);
    }

    public function testParseTopMarginDirective()
    {
        $result = $this->parser->parse('%%topmargin 1.0cm');
        $this->assertEquals('topmargin', $result['directive']);
        $this->assertEquals('1.0cm', $result['value']);
        $this->assertEquals('top', $result['margin_type']);
        $this->assertEquals('1.0', $result['dimension']);
        $this->assertEquals('cm', $result['unit']);
    }

    public function testParseBottomMarginDirective()
    {
        $result = $this->parser->parse('%%bottommargin 1.2cm');
        $this->assertEquals('bottommargin', $result['directive']);
        $this->assertEquals('1.2cm', $result['value']);
        $this->assertEquals('bottom', $result['margin_type']);
        $this->assertEquals('1.2', $result['dimension']);
        $this->assertEquals('cm', $result['unit']);
    }

    public function testValidateValidMarginDirective()
    {
        $this->assertTrue($this->parser->validate('%%leftmargin 1.5cm'));
        $this->assertTrue($this->parser->validate('%%rightmargin 2.0in'));
        $this->assertTrue($this->parser->validate('%%topmargin 1.0mm'));
        $this->assertTrue($this->parser->validate('%%bottommargin 0.5pt'));
    }

    public function testValidateInvalidMarginDirective()
    {
        $this->assertFalse($this->parser->validate('%%leftmargin'));
        $this->assertFalse($this->parser->validate('%%leftmargin invalid'));
        $this->assertFalse($this->parser->validate('%%invalidmargin 1.5cm'));
    }
}