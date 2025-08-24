<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\AbcBeat;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Ksfraser\PhpabcCanntaireachd\AbcBeat
 */
class AbcBeatTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new AbcBeat();
        $this->assertInstanceOf(AbcBeat::class, $obj);
    }
}
