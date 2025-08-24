<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\AbcBarline;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Ksfraser\PhpabcCanntaireachd\AbcBarline
 */
class AbcBarlineTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new AbcBarline();
        $this->assertInstanceOf(AbcBarline::class, $obj);
    }
}
