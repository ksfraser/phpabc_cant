<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Header\AbcHeaderU;

class AbcHeaderUTest extends TestCase {
    public function testLabelIsU() {
        $this->assertEquals('U', AbcHeaderU::$label);
    }
}
