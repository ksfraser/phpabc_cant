<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Render\StartRepeatBarLineRenderer;

class StartRepeatBarLineRendererTest extends TestCase {
    public function testRenderReturnsStartRepeatBar() {
        $renderer = new StartRepeatBarLineRenderer();
        $this->assertEquals('|:', $renderer->render());
    }
}
