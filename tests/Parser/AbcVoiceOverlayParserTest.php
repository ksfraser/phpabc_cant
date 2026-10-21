<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Parser\AbcVoiceOverlayParser;
class AbcVoiceOverlayParserTest extends TestCase {
    public function testParseVoiceOverlay() {
        $parser = new AbcVoiceOverlayParser();
        $this->assertEquals('&', $parser->parse('C&'));
        $this->assertNull($parser->parse('C'));
    }
}
