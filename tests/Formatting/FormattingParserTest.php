<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\AbcTune;

/**
 * Test class for FormattingParser
 */
class FormattingParserTest extends TestCase {
    private $parser;

    protected function setUp(): void {
        $this->parser = new FormattingParser();
    }

    public function testCanParseFormattingDirectives() {
        $this->assertTrue($this->parser->canParse('%%pagewidth 8.5in'));
        $this->assertTrue($this->parser->canParse('%%landscape'));
        $this->assertTrue($this->parser->canParse('%%leftmargin 1cm'));
        $this->assertTrue($this->parser->canParse('%%scale 0.8'));
        $this->assertTrue($this->parser->canParse('%%staffwidth 600'));
    }

    public function testCannotParseNonFormattingDirectives() {
        $this->assertFalse($this->parser->canParse('%%MIDI program 1'));
        $this->assertFalse($this->parser->canParse('X:1'));
        $this->assertFalse($this->parser->canParse('T:Test Tune'));
    }

    public function testParsePageWidth() {
        $tune = new AbcTune();
        $result = $this->parser->parse('%%pagewidth 8.5in', $tune);

        $this->assertTrue($result);
        $lines = $tune->getLines();
        $this->assertCount(1, $lines);
        $this->assertEquals('%% pagewidth 8.5in', $lines[0]->render());
    }

    public function testParseLandscape() {
        $tune = new AbcTune();
        $result = $this->parser->parse('%%landscape', $tune);

        $this->assertTrue($result);
        $lines = $tune->getLines();
        $this->assertCount(1, $lines);
        $this->assertEquals('%% landscape on', $lines[0]->render());
    }

    public function testParseUnknownFormattingDirective() {
        $tune = new AbcTune();
        $result = $this->parser->parse('%%unknown_directive value', $tune);

        $this->assertTrue($result);
        $lines = $tune->getLines();
        $this->assertCount(1, $lines);
        $this->assertEquals('%% unknown_directive value', $lines[0]->render());
    }

    public function testValidateFormattingDirectives() {
        $this->assertTrue($this->parser->validate('%%pagewidth 8.5in'));
        $this->assertTrue($this->parser->validate('%%landscape'));
        $this->assertTrue($this->parser->validate('%%leftmargin 1cm'));
    }
}