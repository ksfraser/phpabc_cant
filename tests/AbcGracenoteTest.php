<?php
namespace ksfraser\PhpabcCanntaireachd\Tests;
use ksfraser\PhpabcCanntaireachd\AbcGracenote;
use PHPUnit\Framework\TestCase;
/**
 * @covers \ksfraser\PhpabcCanntaireachd\AbcGracenote
 */
class AbcGracenoteTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new AbcGracenote('G');
        $this->assertInstanceOf(AbcGracenote::class, $obj);
    }
}
