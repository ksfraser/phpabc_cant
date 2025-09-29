<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Parser\NoteLengthParser;

class NoteLengthParserTest extends TestCase {
    public function testParseNoteLength() {
        $parser = new NoteLengthParser();
        $result = $parser->parse('C2');
        $this->assertEquals('2', $result);
    }
}
