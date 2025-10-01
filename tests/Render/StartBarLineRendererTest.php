<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Render\StartBarLineRenderer;

class StartBarLineRendererTest extends TestCase {
    public function testRenderReturnsStartBar() {
        $renderer = new StartBarLineRenderer();
        $this->assertEquals('[:', $renderer->render());
    }
}
