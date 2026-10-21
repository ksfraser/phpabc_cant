<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Header\AbcHeaderR;

class AbcHeaderRTest extends TestCase {
    public function testLabelIsR() {
        $this->assertEquals('R', AbcHeaderR::$label);
    }
}
