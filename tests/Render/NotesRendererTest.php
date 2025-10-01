<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Render\NotesRenderer;

class NotesRendererTest extends TestCase {
    public function testRenderWithNotes() {
        $renderer = new NotesRenderer();
        $this->assertEquals('A B C', $renderer->render(['A', 'B', 'C']));
    }

    public function testRenderWithEmptyArray() {
        $renderer = new NotesRenderer();
        $this->assertEquals('', $renderer->render([]));
    }
}
