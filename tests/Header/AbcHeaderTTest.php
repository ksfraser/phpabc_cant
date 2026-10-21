<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Header\AbcHeaderT;

class AbcHeaderTTest extends TestCase {
    public function testLabelIsT() {
        $this->assertEquals('T', AbcHeaderT::$label);
    }
}
