<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Header\AbcHeaderN;

class AbcHeaderNTest extends TestCase {
    public function testLabelIsN() {
        $this->assertEquals('N', AbcHeaderN::$label);
    }
}
