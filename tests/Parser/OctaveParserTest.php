<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Parser\OctaveParser;

class OctaveParserTest extends TestCase {
    public function testParseOctave() {
        $parser = new OctaveParser();
        $result = $parser->parse("C''");
        $this->assertEquals("''", $result);
    }
}
