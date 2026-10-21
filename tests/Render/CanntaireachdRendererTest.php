<?php
use PHPUnit\Framework\TestCase;
use ksfraser\PhpabcCanntaireachd\Render\CanntaireachdRenderer;

class CanntaireachdRendererTest extends TestCase {
    public function testRenderWithCanntaireachd() {
        $renderer = new CanntaireachdRenderer();
        $this->assertEquals('W:foo', $renderer->render('foo'));
    }

    public function testRenderWithEmptyString() {
        $renderer = new CanntaireachdRenderer();
        $this->assertEquals('', $renderer->render(''));
    }
}
