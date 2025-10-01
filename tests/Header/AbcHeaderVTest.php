<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderV;

class AbcHeaderVTest extends TestCase {
    public function testLabelIsV() {
        $this->assertEquals('V', AbcHeaderV::$label);
    }
}
