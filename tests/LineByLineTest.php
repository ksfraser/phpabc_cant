<?php
namespace ksfraser\PhpabcCanntaireachd\Tests;
use ksfraser\PhpabcCanntaireachd\LineByLine;
use PHPUnit\Framework\TestCase;
/**
 * @covers \ksfraser\PhpabcCanntaireachd\LineByLine
 */
class LineByLineTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new LineByLine();
        $this->assertInstanceOf(LineByLine::class, $obj);
    }
}
