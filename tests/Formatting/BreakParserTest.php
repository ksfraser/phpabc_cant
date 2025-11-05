<?php

namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use PHPUnit\Framework\TestCase;

class BreakParserTest extends TestCase
{
    private BreakParser $parser;

    protected function setUp(): void
    {
        $this->parser = new BreakParser();
    }

    public function testCanParseValidBreakDirectives()
    {
        $this->assertTrue($this->parser->canParse('%%staffbreak'));
        $this->assertTrue($this->parser->canParse('%%linebreak'));
        $this->assertTrue($this->parser->canParse('%%staffbreak force'));
        $this->assertTrue($this->parser->canParse('%%linebreak 2'));
    }

    public function testCannotParseInvalidBreakDirectives()
    {
        $this->assertFalse($this->parser->canParse('%%staffbreak invalid'));
        $this->assertFalse($this->parser->canParse('%%pagewidth 8.5in'));
        $this->assertFalse($this->parser->canParse('%%scale 0.8'));
    }

    public function testParseStaffBreakDirective()
    {
        $result = $this->parser->parse('%%staffbreak', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testParseLineBreakDirective()
    {
        $result = $this->parser->parse('%%linebreak', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testParseStaffBreakWithParameter()
    {
        $result = $this->parser->parse('%%staffbreak force', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testParseLineBreakWithNumber()
    {
        $result = $this->parser->parse('%%linebreak 2', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testValidateValidBreakDirective()
    {
        $this->assertTrue($this->parser->validate('%%staffbreak'));
        $this->assertTrue($this->parser->validate('%%linebreak'));
        $this->assertTrue($this->parser->validate('%%staffbreak force'));
        $this->assertTrue($this->parser->validate('%%linebreak 2'));
        $this->assertTrue($this->parser->validate('%%staffbreak 1.5'));
    }

    public function testValidateInvalidBreakDirective()
    {
        $this->assertFalse($this->parser->validate('%%staffbreak invalid'));
        $this->assertFalse($this->parser->validate('%%linebreak bad'));
        $this->assertFalse($this->parser->validate('%%invalidbreak'));
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