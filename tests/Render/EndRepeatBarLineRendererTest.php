<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Render\EndRepeatBarLineRenderer;

class EndRepeatBarLineRendererTest extends TestCase {
    public function testRenderReturnsEndRepeatBar() {
        $renderer = new EndRepeatBarLineRenderer();
        $this->assertEquals(':|', $renderer->render());
    }
}
