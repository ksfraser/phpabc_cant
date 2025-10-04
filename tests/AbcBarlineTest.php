<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
// use Ksfraser\PhpabcCanntaireachd\AbcBarline; // removed
// use Ksfraser\PhpabcCanntaireachd\Tune\AbcBarline; // use fully qualified name instead
use PHPUnit\Framework\TestCase;
/**
    * @covers \Ksfraser\PhpabcCanntaireachd\Tune\AbcBarline
 */
class AbcBarlineTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new \Ksfraser\PhpabcCanntaireachd\Tune\AbcBarline();
        $this->assertInstanceOf(\Ksfraser\PhpabcCanntaireachd\Tune\AbcBarline::class, $obj);
    }
}
