<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Header\AbcHeaderQ;

class AbcHeaderQTest extends TestCase {
    public function testLabelIsQ() {
        $this->assertEquals('Q', AbcHeaderQ::$label);
    }
}
