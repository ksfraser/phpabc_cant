<?php

namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use PHPUnit\Framework\TestCase;

class TextParserTest extends TestCase
{
    private TextParser $parser;

    protected function setUp(): void
    {
        $this->parser = new TextParser();
    }

    public function testCanParseValidTextDirectives()
    {
        $this->assertTrue($this->parser->canParse('%%text This is some text'));
        $this->assertTrue($this->parser->canParse('%%center Centered text'));
        $this->assertTrue($this->parser->canParse('%%text Multiple words here'));
    }

    public function testCannotParseInvalidTextDirectives()
    {
        $this->assertFalse($this->parser->canParse('%%text'));
        $this->assertFalse($this->parser->canParse('%%pagewidth 8.5in'));
        $this->assertFalse($this->parser->canParse('%%scale 0.8'));
    }

    public function testParseValidTextDirective()
    {
        $result = $this->parser->parse('%%text This is some text', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testParseCenterDirective()
    {
        $result = $this->parser->parse('%%center Centered text', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testParseTextWithSpecialCharacters()
    {
        $result = $this->parser->parse('%%text Text with 123 & symbols!', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testValidateValidTextDirective()
    {
        $this->assertTrue($this->parser->validate('%%text This is some text'));
        $this->assertTrue($this->parser->validate('%%center Centered text'));
        $this->assertTrue($this->parser->validate('%%text Multiple words here'));
    }

    public function testValidateInvalidTextDirective()
    {
        $this->assertFalse($this->parser->validate('%%text'));
        $this->assertFalse($this->parser->validate('%%center'));
        $this->assertFalse($this->parser->validate('%%invalid This is text'));
    }

    private function createMockTune()
    {
        return new class {
            public $lines = [];
            public function add($line) {
                $this->lines[] = $line;
            }
        };
    }
}