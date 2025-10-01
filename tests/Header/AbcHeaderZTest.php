<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderZ;

class AbcHeaderZTest extends TestCase {
    public function testLabelIsZ() {
        $this->assertEquals('Z', AbcHeaderZ::$label);
    }
}
