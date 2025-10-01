<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderMacro;

class AbcHeaderMacroTest extends TestCase {
    public function testLabelIsLowercaseM() {
        $this->assertEquals('m', AbcHeaderMacro::$label);
    }
}
