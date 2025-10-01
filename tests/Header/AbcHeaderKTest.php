<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderK;

class AbcHeaderKTest extends TestCase {
    public function testLabelIsK() {
        $this->assertEquals('K', AbcHeaderK::$label);
    }
}
