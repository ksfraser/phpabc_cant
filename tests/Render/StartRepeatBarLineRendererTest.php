<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Render\StartRepeatBarLineRenderer;

class StartRepeatBarLineRendererTest extends TestCase {
    public function testRenderReturnsStartRepeatBar() {
        $renderer = new StartRepeatBarLineRenderer();
        $this->assertEquals('|:', $renderer->render());
    }
}
