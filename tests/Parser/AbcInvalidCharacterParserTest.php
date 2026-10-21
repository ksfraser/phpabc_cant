<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Parser\AbcInvalidCharacterParser;
class AbcInvalidCharacterParserTest extends TestCase {
    public function testParseInvalidCharacter() {
        $parser = new AbcInvalidCharacterParser();
    $this->assertTrue($parser->parse("A@#%$\u{2603}")); // Unicode snowman
        $this->assertFalse($parser->parse('A B C'));
    }
}
