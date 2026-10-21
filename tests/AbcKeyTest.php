<?php
namespace ksfraser\PhpabcCanntaireachd\Tests;
use ksfraser\PhpabcCanntaireachd\AbcKey;
use PHPUnit\Framework\TestCase;
/**
 * @covers \ksfraser\PhpabcCanntaireachd\AbcKey
 */
class AbcKeyTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new AbcKey();
        $this->assertInstanceOf(AbcKey::class, $obj);
    }
}
