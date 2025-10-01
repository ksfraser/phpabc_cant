<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\BodyLineHandler\NoteHandler;

class NoteHandlerTest extends TestCase {
    public function testMatchesReturnsTrueForNoteLine() {
        $handler = new NoteHandler();
        $this->assertTrue($handler->matches('C D E'));
    }

    public function testMatchesReturnsFalseForHeaderLine() {
        $handler = new NoteHandler();
        $this->assertFalse($handler->matches('V:1'));
    }

    public function testHandleAddsNoteToBar() {
        $handler = new NoteHandler();
        $context = (object)[
            'currentVoice' => 'V1',
            'currentBar' => 1,
            'voiceBars' => ['V1' => []],
            'getOrCreateVoice' => function($v) { $this->currentVoice = $v; }
        ];
        $handler->handle($context, 'C D E');
        $this->assertEquals(['C D E'], $context->voiceBars['V1'][1]->getNotes());
    }
}
