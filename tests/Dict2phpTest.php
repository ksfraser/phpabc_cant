<?php
namespace ksfraser\PhpabcCanntaireachd\Tests;
use ksfraser\PhpabcCanntaireachd\Dict2php;
use PHPUnit\Framework\TestCase;
/**
 * @covers \ksfraser\PhpabcCanntaireachd\Dict2php
 */
class Dict2phpTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new Dict2php();
        $this->assertInstanceOf(Dict2php::class, $obj);
    }
}
