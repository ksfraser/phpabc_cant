<?php

namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use PHPUnit\Framework\TestCase;

class FontParserTest extends TestCase
{
    private FontParser $parser;

    protected function setUp(): void
    {
        $this->parser = new FontParser();
    }

    public function testCanParseValidFontDirectives()
    {
        $this->assertTrue($this->parser->canParse('%%titlefont Times-Roman 24'));
        $this->assertTrue($this->parser->canParse('%%composerfont Arial 12'));
        $this->assertTrue($this->parser->canParse('%%subtitlefont Helvetica-Bold 18'));
        $this->assertTrue($this->parser->canParse('%%tempofont Courier 14'));
        $this->assertTrue($this->parser->canParse('%%partsfont Times-Italic 16'));
        $this->assertTrue($this->parser->canParse('%%voicefont Garamond 20'));
        $this->assertTrue($this->parser->canParse('%%gchordfont Symbol 12'));
        $this->assertTrue($this->parser->canParse('%%annotationfont Arial 10'));
    }

    public function testCannotParseInvalidFontDirectives()
    {
        $this->assertFalse($this->parser->canParse('%%invalidfont Times 12'));
        $this->assertFalse($this->parser->canParse('%%titlefont'));
        $this->assertFalse($this->parser->canParse('%%pagewidth 8.5in'));
        $this->assertFalse($this->parser->canParse('%%scale 0.8'));
    }

    public function testParseValidFontDirective()
    {
        $result = $this->parser->parse('%%titlefont Times-Roman 24');
        $this->assertEquals('titlefont', $result['directive']);
        $this->assertEquals('Times-Roman 24', $result['value']);
        $this->assertEquals('Times-Roman', $result['font_name']);
        $this->assertEquals('24', $result['font_size']);
    }

    public function testParseFontDirectiveWithMultipleWords()
    {
        $result = $this->parser->parse('%%composerfont Times New Roman-Bold 18');
        $this->assertEquals('composerfont', $result['directive']);
        $this->assertEquals('Times New Roman-Bold 18', $result['value']);
        $this->assertEquals('Times New Roman-Bold', $result['font_name']);
        $this->assertEquals('18', $result['font_size']);
    }

    public function testValidateValidFontDirective()
    {
        $this->assertTrue($this->parser->validate('%%titlefont Times-Roman 24'));
        $this->assertTrue($this->parser->validate('%%composerfont Arial 12'));
    }

    public function testValidateInvalidFontDirective()
    {
        $this->assertFalse($this->parser->validate('%%invalidfont Times 12'));
        $this->assertFalse($this->parser->validate('%%titlefont'));
        $this->assertFalse($this->parser->validate('%%titlefont Times'));
    }
}