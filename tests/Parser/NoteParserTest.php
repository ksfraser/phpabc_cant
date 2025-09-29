<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Parser\NoteParser;

class NoteParserTest extends TestCase {
    public function testParseNote() {
        $parser = new NoteParser();
        $result = $parser->parse('C');
        $this->assertEquals('C', $result);
    }
}
