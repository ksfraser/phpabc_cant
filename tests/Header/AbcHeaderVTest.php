<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Header\AbcHeaderV;

class AbcHeaderVTest extends TestCase {
    public function testLabelIsV() {
        $this->assertEquals('V', AbcHeaderV::$label);
    }
}
