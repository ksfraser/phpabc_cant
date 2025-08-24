<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\AbcGracenote;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Ksfraser\PhpabcCanntaireachd\AbcGracenote
 */
class AbcGracenoteTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new AbcGracenote('G');
        $this->assertInstanceOf(AbcGracenote::class, $obj);
    }
}
