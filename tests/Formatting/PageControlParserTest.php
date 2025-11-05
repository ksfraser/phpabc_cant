<?php

namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use PHPUnit\Framework\TestCase;

class PageControlParserTest extends TestCase
{
    private PageControlParser $parser;

    protected function setUp(): void
    {
        $this->parser = new PageControlParser();
    }

    public function testCanParseValidPageControlDirectives()
    {
        $this->assertTrue($this->parser->canParse('%%newpage'));
        $this->assertTrue($this->parser->canParse('%%continueall'));
        $this->assertTrue($this->parser->canParse('%%breakall'));
        $this->assertTrue($this->parser->canParse('%%newpage on'));
        $this->assertTrue($this->parser->canParse('%%continueall off'));
    }

    public function testCannotParseInvalidPageControlDirectives()
    {
        $this->assertFalse($this->parser->canParse('%%invalidpage'));
        $this->assertFalse($this->parser->canParse('%%pagewidth 8.5in'));
        $this->assertFalse($this->parser->canParse('%%scale 0.8'));
    }

    public function testParseNewPageDirective()
    {
        $result = $this->parser->parse('%%newpage', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testParseContinueAllDirective()
    {
        $result = $this->parser->parse('%%continueall', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testParseBreakAllDirective()
    {
        $result = $this->parser->parse('%%breakall', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testParseNewPageWithParameter()
    {
        $result = $this->parser->parse('%%newpage on', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testValidateValidPageControlDirective()
    {
        $this->assertTrue($this->parser->validate('%%newpage'));
        $this->assertTrue($this->parser->validate('%%continueall'));
        $this->assertTrue($this->parser->validate('%%breakall on'));
        $this->assertTrue($this->parser->validate('%%newpage off'));
    }

    public function testValidateInvalidPageControlDirective()
    {
        $this->assertFalse($this->parser->validate('%%newpage invalid'));
        $this->assertFalse($this->parser->validate('%%invalidpage'));
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