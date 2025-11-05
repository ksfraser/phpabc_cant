<?php

namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use PHPUnit\Framework\TestCase;

class PageHeightParserTest extends TestCase
{
    private PageHeightParser $parser;

    protected function setUp(): void
    {
        $this->parser = new PageHeightParser();
    }

    public function testCanParseValidPageHeightDirectives()
    {
        $this->assertTrue($this->parser->canParse('%%pagesheight 11in'));
        $this->assertTrue($this->parser->canParse('%%pagesheight 29.7cm'));
        $this->assertTrue($this->parser->canParse('%%pagesheight 297mm'));
    }

    public function testCannotParseInvalidPageHeightDirectives()
    {
        $this->assertFalse($this->parser->canParse('%%pagesheight'));
        $this->assertFalse($this->parser->canParse('%%pagewidth 8.5in'));
        $this->assertFalse($this->parser->canParse('%%scale 0.8'));
    }

    public function testParseValidPageHeightDirective()
    {
        $result = $this->parser->parse('%%pagesheight 11in', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testParsePageHeightWithDifferentUnits()
    {
        $result = $this->parser->parse('%%pagesheight 29.7cm', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testValidateValidPageHeightDirective()
    {
        $this->assertTrue($this->parser->validate('%%pagesheight 11in'));
        $this->assertTrue($this->parser->validate('%%pagesheight 29.7cm'));
        $this->assertTrue($this->parser->validate('%%pagesheight 297mm'));
        $this->assertTrue($this->parser->validate('%%pagesheight 100pt'));
    }

    public function testValidateInvalidPageHeightDirective()
    {
        $this->assertFalse($this->parser->validate('%%pagesheight'));
        $this->assertFalse($this->parser->validate('%%pagesheight invalid'));
        $this->assertFalse($this->parser->validate('%%pagesheight 0')); // zero not allowed
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