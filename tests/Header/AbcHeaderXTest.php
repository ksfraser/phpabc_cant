<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderX;

class AbcHeaderXTest extends TestCase {
    public function testLabelIsX() {
        $this->assertEquals('X', AbcHeaderX::$label);
    }
}
