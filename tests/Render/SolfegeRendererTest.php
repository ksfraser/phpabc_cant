<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Render\SolfegeRenderer;

class SolfegeRendererTest extends TestCase {
    public function testRenderWithSolfege() {
        $renderer = new SolfegeRenderer();
        $this->assertEquals('S:do re mi', $renderer->render('do re mi'));
    }

    public function testRenderWithEmptyString() {
        $renderer = new SolfegeRenderer();
        $this->assertEquals('', $renderer->render(''));
    }
}
