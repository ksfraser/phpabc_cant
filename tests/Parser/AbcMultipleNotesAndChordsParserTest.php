<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Parser\AbcMultipleNotesAndChordsParser;
class AbcMultipleNotesAndChordsParserTest extends TestCase {
    public function testParseMultipleNotesAndChords() {
        $parser = new AbcMultipleNotesAndChordsParser();
    $this->assertEquals(['A','B','C','C','E','G','[CEG]'], $parser->parse('A B C [CEG]'));
        $this->assertNull($parser->parse('A'));
    }
}
