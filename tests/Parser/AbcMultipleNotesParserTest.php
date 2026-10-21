<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Parser\AbcMultipleNotesParser;
class AbcMultipleNotesParserTest extends TestCase {
    public function testParseMultipleNotes() {
        $parser = new AbcMultipleNotesParser();
        $this->assertEquals(['A','B','C'], $parser->parse('A B C'));
        $this->assertNull($parser->parse('A'));
    }
}
