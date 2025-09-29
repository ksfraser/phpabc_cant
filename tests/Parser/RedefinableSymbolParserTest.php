<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Parser\RedefinableSymbolParser;
use Ksfraser\PhpabcCanntaireachd\NoteElement\RedefinableSymbol;
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
