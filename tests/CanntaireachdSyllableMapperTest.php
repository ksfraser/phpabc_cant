<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\CanntaireachdSyllableMapper;
use Ksfraser\PhpabcCanntaireachd\Exceptions\TokenMappingException;

class CanntaireachdSyllableMapperTest extends TestCase {
    public function testMapTokenReturnsSyllable() {
        $dict = [ '{g}A' => 'hen', '{g}B' => 'o' ];
        $this->assertEquals('hen', CanntaireachdSyllableMapper::mapToken('{g}A3', $dict));
        $this->assertEquals('o', CanntaireachdSyllableMapper::mapToken('{g}B2', $dict));
    }
    public function testMapTokenThrowsOnMissing() {
        $dict = [ '{g}A' => 'hen' ];
        $this->expectException(TokenMappingException::class);
        CanntaireachdSyllableMapper::mapToken('{g}X3', $dict);
    }
}
