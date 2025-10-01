<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderO;

class AbcHeaderOTest extends TestCase {
    public function testLabelIsO() {
        $this->assertEquals('O', AbcHeaderO::$label);
    }
}
