<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Render\BarLineRenderer;

class BarLineRendererTest extends TestCase {
    public function testRenderReturnsBarLineType() {
        $renderer = new BarLineRenderer('|:');
        $this->assertEquals('|:', $renderer->render());
    }

    public function testDefaultBarLineTypeIsPipe() {
        $renderer = new BarLineRenderer();
        $this->assertEquals('|', $renderer->render());
    }

    public function testGetSupportedBarLinesReturnsArray() {
        $result = BarLineRenderer::getSupportedBarLines();
        $this->assertIsArray($result);
        $this->assertContains('|', $result);
    }
}
