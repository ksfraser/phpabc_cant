<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;

use Ksfraser\PhpabcCanntaireachd\TokenNormalizer;
use Ksfraser\PhpabcCanntaireachd\TokenToCanntMapper;
use Ksfraser\PhpabcCanntaireachd\Exceptions\TokenMappingException;
use PHPUnit\Framework\TestCase;

class TokenMappingHelpersTest extends TestCase {
    public static function setUpBeforeClass(): void {
        require_once __DIR__ . '/../src/Ksfraser/PhpabcCanntaireachd/TokenMappingHelpers.php';
    }
    public function testNormalizerStripsDuration() {
        $this->assertEquals('{g}A', TokenNormalizer::normalize('{g}A3'));
        $this->assertEquals('{g}B', TokenNormalizer::normalize('{g}B2'));
        $this->assertEquals('{d}c', TokenNormalizer::normalize('{d}c1'));
        $this->assertEquals('A', TokenNormalizer::normalize('A4'));
    }
    public function testNormalizerThrowsOnEmpty() {
        $this->expectException(TokenMappingException::class);
        TokenNormalizer::normalize('');
    }
    public function testMapperReturnsMapping() {
        $dict = [
            '{g}A' => 'hen',
            '{g}B' => 'o',
            '{d}c' => 'do',
        ];
        $mapper = new TokenToCanntMapper($dict);
        $this->assertEquals('hen', $mapper->map('{g}A3'));
        $this->assertEquals('o', $mapper->map('{g}B2'));
        $this->assertEquals('do', $mapper->map('{d}c1'));
    }
    public function testMapperThrowsOnMissing() {
        $dict = [ '{g}A' => 'hen' ];
        $mapper = new TokenToCanntMapper($dict);
        $this->expectException(TokenMappingException::class);
        $mapper->map('{g}X3');
    }
}
