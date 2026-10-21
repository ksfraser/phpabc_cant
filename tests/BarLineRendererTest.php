<?php
namespace ksfraser\PhpabcCanntaireachd\Tests;
use ksfraser\PhpabcCanntaireachd\Render\SimpleBarLineRenderer;
use ksfraser\PhpabcCanntaireachd\Render\DoubleBarLineRenderer;
use ksfraser\PhpabcCanntaireachd\Render\StartRepeatBarLineRenderer;
use ksfraser\PhpabcCanntaireachd\Render\EndRepeatBarLineRenderer;
use ksfraser\PhpabcCanntaireachd\Render\StartBarLineRenderer;
use ksfraser\PhpabcCanntaireachd\Render\EndBarLineRenderer;
use PHPUnit\Framework\TestCase;

class BarLineRendererTest extends TestCase {
    public function testSimpleBarLine() {
        $r = new SimpleBarLineRenderer();
        $this->assertEquals('|', $r->render());
    }
    public function testDoubleBarLine() {
        $r = new DoubleBarLineRenderer();
        $this->assertEquals('||', $r->render());
    }
    public function testStartRepeatBarLine() {
        $r = new StartRepeatBarLineRenderer();
        $this->assertEquals('|:', $r->render());
    }
    public function testEndRepeatBarLine() {
        $r = new EndRepeatBarLineRenderer();
        $this->assertEquals(':|', $r->render());
    }
    public function testStartBarLine() {
        $r = new StartBarLineRenderer();
        $this->assertEquals('[:', $r->render());
    }
    public function testEndBarLine() {
        $r = new EndBarLineRenderer();
        $this->assertEquals(':]', $r->render());
    }
}
