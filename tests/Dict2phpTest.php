<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\Dict2php;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Ksfraser\PhpabcCanntaireachd\Dict2php
 */
class Dict2phpTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new Dict2php();
        $this->assertInstanceOf(Dict2php::class, $obj);
    }
}
