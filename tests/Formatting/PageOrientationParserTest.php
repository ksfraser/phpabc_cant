<?php

namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use PHPUnit\Framework\TestCase;

class PageOrientationParserTest extends TestCase
{
    private PageOrientationParser $parser;

    protected function setUp(): void
    {
        $this->parser = new PageOrientationParser();
    }

    public function testCanParseValidOrientationDirectives()
    {
        $this->assertTrue($this->parser->canParse('%%landscape'));
        $this->assertTrue($this->parser->canParse('%%portrait'));
        $this->assertTrue($this->parser->canParse('%%landscape on'));
        $this->assertTrue($this->parser->canParse('%%portrait off'));
    }

    public function testCannotParseInvalidOrientationDirectives()
    {
        $this->assertFalse($this->parser->canParse('%%landscape invalid'));
        $this->assertFalse($this->parser->canParse('%%portrait maybe'));
        $this->assertFalse($this->parser->canParse('%%pagewidth 8.5in'));
        $this->assertFalse($this->parser->canParse('%%scale 0.8'));
    }

    public function testParseLandscapeDirective()
    {
        $result = $this->parser->parse('%%landscape');
        $this->assertEquals('landscape', $result['directive']);
        $this->assertEquals('on', $result['state']); // default state
    }

    public function testParsePortraitDirective()
    {
        $result = $this->parser->parse('%%portrait');
        $this->assertEquals('portrait', $result['directive']);
        $this->assertEquals('on', $result['state']); // default state
    }

    public function testParseLandscapeWithState()
    {
        $result = $this->parser->parse('%%landscape off');
        $this->assertEquals('landscape', $result['directive']);
        $this->assertEquals('off', $result['state']);
    }

    public function testParsePortraitWithState()
    {
        $result = $this->parser->parse('%%portrait on');
        $this->assertEquals('portrait', $result['directive']);
        $this->assertEquals('on', $result['state']);
    }

    public function testValidateValidOrientationDirective()
    {
        $this->assertTrue($this->parser->validate('%%landscape'));
        $this->assertTrue($this->parser->validate('%%portrait on'));
        $this->assertTrue($this->parser->validate('%%landscape off'));
    }

    public function testValidateInvalidOrientationDirective()
    {
        $this->assertFalse($this->parser->validate('%%landscape invalid'));
        $this->assertFalse($this->parser->validate('%%portrait maybe'));
    }
}