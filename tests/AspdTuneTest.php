<?php
namespace ksfraser\PhpabcCanntaireachd\Tests;
use ksfraser\PhpabcCanntaireachd\AspdTune;
use PHPUnit\Framework\TestCase;
/**
 * @covers \ksfraser\PhpabcCanntaireachd\AspdTune
 */
class AspdTuneTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new AspdTune();
        $this->assertInstanceOf(AspdTune::class, $obj);
    }
}
