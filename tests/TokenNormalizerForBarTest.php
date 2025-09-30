<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\TokenNormalizerForBar;

class TokenNormalizerForBarTest extends TestCase {
    public function testNormalizeTokensStripsDurations() {
        $tokens = ['{g}A3', '{g}B2', '{d}c1', 'A4', '|', '||'];
        $expected = ['{g}A', '{g}B', '{d}c', 'A', '|', '||'];
        $result = TokenNormalizerForBar::normalizeTokens($tokens);
        $this->assertEquals($expected, $result);
    }
    public function testNormalizeTokensFallback() {
        $tokens = ['{g}A3', 'X', 'B'];
        $expected = ['{g}A', 'X', 'B'];
        $result = TokenNormalizerForBar::normalizeTokens($tokens);
        $this->assertEquals($expected, $result);
    }
}
