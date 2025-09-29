<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Parser\GraceNotesParser;

class GraceNotesParserTest extends TestCase {
    public function testParseGraceNotes() {
        $parser = new GraceNotesParser();
        $result = $parser->parse('{G A B}C');
        $this->assertEquals(['G', 'A', 'B'], $result);
    }
}
