<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\LineByLine;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Ksfraser\PhpabcCanntaireachd\LineByLine
 */
class LineByLineTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new LineByLine();
        $this->assertInstanceOf(LineByLine::class, $obj);
    }
}
