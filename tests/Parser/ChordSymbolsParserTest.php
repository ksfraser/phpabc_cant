<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Parser\ChordSymbolsParser;

class ChordSymbolsParserTest extends TestCase {
    public function testParseChordSymbol() {
        $parser = new ChordSymbolsParser();
        $result = $parser->parse('"Am"C');
        $this->assertEquals('Am', $result);
    }
}
