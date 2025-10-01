<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Render\SimpleBarLineRenderer;

class SimpleBarLineRendererTest extends TestCase {
    public function testRenderReturnsSimpleBar() {
        $renderer = new SimpleBarLineRenderer();
        $this->assertEquals('|', $renderer->render());
    }
}
