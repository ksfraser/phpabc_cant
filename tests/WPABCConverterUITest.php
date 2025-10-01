<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\WPABCConverterUI;

class WPABCConverterUITest extends TestCase {
    public function testRenderReturnsVoid() {
        $ui = new WPABCConverterUI();
        $this->assertNull($ui->render());
    }
}
