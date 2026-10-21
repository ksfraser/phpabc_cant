<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Render\DoubleBarLineRenderer;

class DoubleBarLineRendererTest extends TestCase {
    public function testRenderReturnsDoubleBar() {
        $renderer = new DoubleBarLineRenderer();
        $this->assertEquals('||', $renderer->render());
    }
}
