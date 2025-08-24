<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\AspdTune;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Ksfraser\PhpabcCanntaireachd\AspdTune
 */
class AspdTuneTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new AspdTune();
        $this->assertInstanceOf(AspdTune::class, $obj);
    }
}
