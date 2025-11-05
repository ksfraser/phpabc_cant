<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\AbcTune;

/**
 * Test class for PageWidthParser
 */
class PageWidthParserTest extends TestCase {
    private $parser;

    protected function setUp(): void {
        $this->parser = new PageWidthParser();
    }

    public function testCanParseValidPageWidth() {
        $this->assertTrue($this->parser->canParse('%%pagewidth 8.5in'));
        $this->assertTrue($this->parser->canParse('%%pagewidth 21cm'));
        $this->assertTrue($this->parser->canParse('%%pagewidth 600pt'));
    }

    public function testCannotParseInvalidDirectives() {
        $this->assertFalse($this->parser->canParse('%%landscape'));
        $this->assertFalse($this->parser->canParse('%%MIDI program 1'));
        $this->assertFalse($this->parser->canParse('X:1'));
    }

    public function testParseValidPageWidth() {
        $tune = new AbcTune();
        $result = $this->parser->parse('%%pagewidth 8.5in', $tune);

        $this->assertTrue($result);
        $lines = $tune->getLines();
        $this->assertCount(1, $lines);
        $this->assertEquals('%% pagewidth 8.5in', $lines[0]->render());
    }

    public function testParseInvalidPageWidth() {
        $tune = new AbcTune();
        $result = $this->parser->parse('%%pagewidth invalid', $tune);

        $this->assertTrue($result);
        $lines = $tune->getLines();
        $this->assertCount(1, $lines);
        $this->assertStringContains('Invalid pagewidth value', $lines[0]->render());
    }

    public function testValidateValidPageWidth() {
        $this->assertTrue($this->parser->validate('%%pagewidth 8.5in'));
        $this->assertTrue($this->parser->validate('%%pagewidth 21cm'));
        $this->assertTrue($this->parser->validate('%%pagewidth 600pt'));
    }

    public function testValidateInvalidPageWidth() {
        $this->assertFalse($this->parser->validate('%%pagewidth invalid'));
        $this->assertFalse($this->parser->validate('%%pagewidth'));
    }
}