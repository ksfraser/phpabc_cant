<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Render\EndBarLineRenderer;

class EndBarLineRendererTest extends TestCase {
    public function testRenderReturnsEndBar() {
        $renderer = new EndBarLineRenderer();
        $this->assertEquals(':]', $renderer->render());
    }
}
