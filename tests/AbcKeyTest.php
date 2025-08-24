<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\AbcKey;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Ksfraser\PhpabcCanntaireachd\AbcKey
 */
class AbcKeyTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new AbcKey();
        $this->assertInstanceOf(AbcKey::class, $obj);
    }
}
