<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Parser\ChordSymbolsParser;

class ChordSymbolsParserTest extends TestCase {
    public function testParseChordSymbol() {
        $parser = new ChordSymbolsParser();
        $result = $parser->parse('"Am"C');
        $this->assertEquals('Am', $result);
    }
}
