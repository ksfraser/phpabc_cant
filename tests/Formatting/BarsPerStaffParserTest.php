<?php

namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use PHPUnit\Framework\TestCase;

class BarsPerStaffParserTest extends TestCase
{
    private BarsPerStaffParser $parser;

    protected function setUp(): void
    {
        $this->parser = new BarsPerStaffParser();
    }

    public function testCanParseValidBarsPerStaffDirectives()
    {
        $this->assertTrue($this->parser->canParse('%%barsperstaff 4'));
        $this->assertTrue($this->parser->canParse('%%barsperstaff 8'));
        $this->assertTrue($this->parser->canParse('%%barsperstaff 12'));
    }

    public function testCannotParseInvalidBarsPerStaffDirectives()
    {
        $this->assertFalse($this->parser->canParse('%%barsperstaff'));
        $this->assertFalse($this->parser->canParse('%%pagewidth 8.5in'));
        $this->assertFalse($this->parser->canParse('%%scale 0.8'));
    }

    public function testParseValidBarsPerStaffDirective()
    {
        $result = $this->parser->parse('%%barsperstaff 4', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testParseBarsPerStaffWithDifferentValues()
    {
        $result = $this->parser->parse('%%barsperstaff 8', $this->createMockTune());
        $this->assertTrue($result);
    }

    public function testValidateValidBarsPerStaffDirective()
    {
        $this->assertTrue($this->parser->validate('%%barsperstaff 4'));
        $this->assertTrue($this->parser->validate('%%barsperstaff 8'));
        $this->assertTrue($this->parser->validate('%%barsperstaff 12'));
        $this->assertTrue($this->parser->validate('%%barsperstaff 16'));
    }

    public function testValidateInvalidBarsPerStaffDirective()
    {
        $this->assertFalse($this->parser->validate('%%barsperstaff'));
        $this->assertFalse($this->parser->validate('%%barsperstaff invalid'));
        $this->assertFalse($this->parser->validate('%%barsperstaff 0')); // zero not allowed
        $this->assertFalse($this->parser->validate('%%barsperstaff -4')); // negative not allowed
        $this->assertFalse($this->parser->validate('%%barsperstaff 4.5')); // decimal not allowed
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