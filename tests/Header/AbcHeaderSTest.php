<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderS;

class AbcHeaderSTest extends TestCase {
    public function testLabelIsS() {
        $this->assertEquals('S', AbcHeaderS::$label);
    }
}
