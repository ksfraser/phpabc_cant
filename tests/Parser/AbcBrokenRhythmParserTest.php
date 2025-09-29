<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Parser\AbcBrokenRhythmParser;
class AbcBrokenRhythmParserTest extends TestCase {
    public function testParseBrokenRhythm() {
        $parser = new AbcBrokenRhythmParser();
        $this->assertEquals(['<'], $parser->parse('C<'));
        $this->assertEquals(['>'], $parser->parse('C>'));
        $this->assertEquals(['<','>'], $parser->parse('C<>'));
        $this->assertNull($parser->parse('C'));
    }
}
