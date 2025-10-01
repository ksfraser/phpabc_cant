<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderR;

class AbcHeaderRTest extends TestCase {
    public function testLabelIsR() {
        $this->assertEquals('R', AbcHeaderR::$label);
    }
}
