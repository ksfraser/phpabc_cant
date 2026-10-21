<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Parser\RedefinableSymbolParser;
use ksfraser\PhpabcCanntaireachd\NoteElement\RedefinableSymbol;
class RedefinableSymbolParserTest extends TestCase {
    public function testParseRedefinableSymbol() {
        $parser = new RedefinableSymbolParser(['T' => 'trill']);
        $result = $parser->parse('CT');
        $this->assertInstanceOf(RedefinableSymbol::class, $result);
        $this->assertEquals('T', $result->getShortcut());
    $this->assertEquals('trill', $result->getName());
        $this->assertNull($parser->parse('C'));
    }
}
