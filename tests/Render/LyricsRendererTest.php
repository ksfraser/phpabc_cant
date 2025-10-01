<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Render\LyricsRenderer;

class LyricsRendererTest extends TestCase {
    public function testRenderWithLyrics() {
        $renderer = new LyricsRenderer();
        $this->assertEquals('w:hello', $renderer->render('hello'));
    }

    public function testRenderWithEmptyString() {
        $renderer = new LyricsRenderer();
        $this->assertEquals('', $renderer->render(''));
    }
}
