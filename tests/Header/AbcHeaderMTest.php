<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Header\AbcHeaderM;

class AbcHeaderMTest extends TestCase {
    public function testLabelIsM() {
        $this->assertEquals('M', AbcHeaderM::$label);
    }
}
