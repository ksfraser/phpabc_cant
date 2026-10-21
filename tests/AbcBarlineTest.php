<?php
namespace ksfraser\PhpabcCanntaireachd\Tests;
use ksfraser\PhpabcCanntaireachd\Tune\AbcBarline;
use PHPUnit\Framework\TestCase;
/**
 * @covers \ksfraser\PhpabcCanntaireachd\Tune\AbcBarline
 */
class AbcBarlineTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new AbcBarline();
        $this->assertInstanceOf(AbcBarline::class, $obj);
    }
}
