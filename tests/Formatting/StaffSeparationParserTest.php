<?php

namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use PHPUnit\Framework\TestCase;

class StaffSeparationParserTest extends TestCase
{
    private StaffSeparationParser $parser;

    protected function setUp(): void
    {
        $this->parser = new StaffSeparationParser();
    }

    public function testCanParseValidStaffSeparationDirectives()
    {
        $this->assertTrue($this->parser->canParse('%%staffsep 20pt'));
        $this->assertTrue($this->parser->canParse('%%sysstaffsep 15cm'));
        $this->assertTrue($this->parser->canParse('%%staffsep 50'));
    }

    public function testCannotParseInvalidStaffSeparationDirectives()
    {
        $this->assertFalse($this->parser->canParse('%%staffsep'));
        $this->assertFalse($this->parser->canParse('%%invalidsep 20pt'));
        $this->assertFalse($this->parser->canParse('%%pagewidth 8.5in'));
        $this->assertFalse($this->parser->canParse('%%scale 0.8'));
    }

    public function testParseStaffSepDirective()
    {
        $result = $this->parser->parse('%%staffsep 20pt', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testParseSysStaffSepDirective()
    {
        $result = $this->parser->parse('%%sysstaffsep 15cm', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testValidateValidStaffSeparationDirective()
    {
        $this->assertTrue($this->parser->validate('%%staffsep 20pt'));
        $this->assertTrue($this->parser->validate('%%sysstaffsep 15cm'));
        $this->assertTrue($this->parser->validate('%%staffsep 50'));
    }

    public function testValidateInvalidStaffSeparationDirective()
    {
        $this->assertFalse($this->parser->validate('%%staffsep'));
        $this->assertFalse($this->parser->validate('%%staffsep invalid'));
        $this->assertFalse($this->parser->validate('%%invalidsep 20pt'));
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