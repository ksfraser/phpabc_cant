<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderMidi;

class AbcHeaderMidiTest extends TestCase {
    public function testLabelIsMidi() {
        $this->assertEquals('%%MIDI', AbcHeaderMidi::$label);
    }
}
