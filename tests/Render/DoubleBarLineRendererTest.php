<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Render\DoubleBarLineRenderer;

class DoubleBarLineRendererTest extends TestCase {
    public function testRenderReturnsDoubleBar() {
        $renderer = new DoubleBarLineRenderer();
        $this->assertEquals('||', $renderer->render());
    }
}
