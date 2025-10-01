<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderT;

class AbcHeaderTTest extends TestCase {
    public function testLabelIsT() {
        $this->assertEquals('T', AbcHeaderT::$label);
    }
}
