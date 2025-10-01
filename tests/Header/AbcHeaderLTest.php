<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderL;

class AbcHeaderLTest extends TestCase {
    public function testLabelIsL() {
        $this->assertEquals('L', AbcHeaderL::$label);
    }
}
