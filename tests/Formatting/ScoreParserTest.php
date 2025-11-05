<?php

namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use PHPUnit\Framework\TestCase;

class ScoreParserTest extends TestCase
{
    private ScoreParser $parser;

    protected function setUp(): void
    {
        $this->parser = new ScoreParser();
    }

    public function testCanParseValidScoreDirectives()
    {
        $this->assertTrue($this->parser->canParse('%%score (1 2)'));
        $this->assertTrue($this->parser->canParse('%%score {3 4}'));
        $this->assertTrue($this->parser->canParse('%%score 1 2 3'));
        $this->assertTrue($this->parser->canParse('%%score (1 2) {3 4}'));
    }

    public function testCannotParseInvalidScoreDirectives()
    {
        $this->assertFalse($this->parser->canParse('%%score'));
        $this->assertFalse($this->parser->canParse('%%pagewidth 8.5in'));
        $this->assertFalse($this->parser->canParse('%%scale 0.8'));
    }

    public function testParseValidScoreDirective()
    {
        $result = $this->parser->parse('%%score (1 2)', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testParseScoreWithBraces()
    {
        $result = $this->parser->parse('%%score {3 4}', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testParseComplexScoreDirective()
    {
        $result = $this->parser->parse('%%score (1 2) {3 4}', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testValidateValidScoreDirective()
    {
        $this->assertTrue($this->parser->validate('%%score (1 2)'));
        $this->assertTrue($this->parser->validate('%%score {3 4}'));
        $this->assertTrue($this->parser->validate('%%score 1 2 3'));
        $this->assertTrue($this->parser->validate('%%score (1 2) {3 4} *'));
    }

    public function testValidateInvalidScoreDirective()
    {
        $this->assertFalse($this->parser->validate('%%score'));
        $this->assertFalse($this->parser->validate('%%score invalid'));
        $this->assertFalse($this->parser->validate('%%score (a b)')); // no digits
        $this->assertFalse($this->parser->validate('%%score [1 2]')); // invalid brackets
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