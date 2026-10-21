<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Header\AbcHeaderO;

class AbcHeaderOTest extends TestCase {
    public function testLabelIsO() {
        $this->assertEquals('O', AbcHeaderO::$label);
    }
}
