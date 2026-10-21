<?php
namespace ksfraser\PhpabcCanntaireachd\Tests;
use ksfraser\PhpabcCanntaireachd\AbcBeat;
use PHPUnit\Framework\TestCase;
/**
 * @covers \ksfraser\PhpabcCanntaireachd\AbcBeat
 */
class AbcBeatTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new AbcBeat();
        $this->assertInstanceOf(AbcBeat::class, $obj);
    }
}
