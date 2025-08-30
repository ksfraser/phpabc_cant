<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\Render\SimpleBarLineRenderer;
use Ksfraser\PhpabcCanntaireachd\Render\DoubleBarLineRenderer;
use Ksfraser\PhpabcCanntaireachd\Render\StartRepeatBarLineRenderer;
use Ksfraser\PhpabcCanntaireachd\Render\EndRepeatBarLineRenderer;
use Ksfraser\PhpabcCanntaireachd\Render\StartBarLineRenderer;
use Ksfraser\PhpabcCanntaireachd\Render\EndBarLineRenderer;
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
