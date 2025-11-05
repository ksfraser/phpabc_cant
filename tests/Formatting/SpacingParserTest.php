<?php

namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use PHPUnit\Framework\TestCase;

class SpacingParserTest extends TestCase
{
    private SpacingParser $parser;

    protected function setUp(): void
    {
        $this->parser = new SpacingParser();
    }

    public function testCanParseValidSpacingDirectives()
    {
        $this->assertTrue($this->parser->canParse('%%vskip 1.5cm'));
        $this->assertTrue($this->parser->canParse('%%musicspace 20pt'));
        $this->assertTrue($this->parser->canParse('%%titlespace 10'));
        $this->assertTrue($this->parser->canParse('%%voicespace 15pt'));
    }

    public function testCannotParseInvalidSpacingDirectives()
    {
        $this->assertFalse($this->parser->canParse('%%vskip'));
        $this->assertFalse($this->parser->canParse('%%pagewidth 8.5in'));
        $this->assertFalse($this->parser->canParse('%%scale 0.8'));
    }

    public function testParseValidSpacingDirective()
    {
        $result = $this->parser->parse('%%vskip 1.5cm', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testParseMusicSpaceDirective()
    {
        $result = $this->parser->parse('%%musicspace 20pt', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testParseTitleSpaceDirective()
    {
        $result = $this->parser->parse('%%titlespace 10', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testValidateValidSpacingDirective()
    {
        $this->assertTrue($this->parser->validate('%%vskip 1.5cm'));
        $this->assertTrue($this->parser->validate('%%musicspace 20pt'));
        $this->assertTrue($this->parser->validate('%%titlespace 10'));
        $this->assertTrue($this->parser->validate('%%voicespace 15pt'));
        $this->assertTrue($this->parser->validate('%%gchordspace 0.5in'));
    }

    public function testValidateInvalidSpacingDirective()
    {
        $this->assertFalse($this->parser->validate('%%vskip'));
        $this->assertFalse($this->parser->validate('%%vskip invalid'));
        $this->assertFalse($this->parser->validate('%%invalidspace 1.5cm'));
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