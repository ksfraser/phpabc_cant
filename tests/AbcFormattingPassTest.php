<?php
namespace Ksfraser\PhpabcCanntaireachd;

use PHPUnit\Framework\TestCase;

/**
 * Test class for AbcFormattingPass
 */
class AbcFormattingPassTest extends TestCase {
    private $pass;

    protected function setUp(): void {
        $this->pass = new AbcFormattingPass();
    }

    public function testProcessStandardizesHeaderSpacing() {
        $lines = [
            'X:1',
            'T:Test Tune',
            'M:4/4',
            'L:1/8',
            'K:D'
        ];

        $result = $this->pass->process($lines);

        // Headers should have space after colon
        $this->assertEquals('X: 1', $result['lines'][0]);
        $this->assertEquals('T: Test Tune', $result['lines'][1]);
        $this->assertEquals('M: 4/4', $result['lines'][2]);
        $this->assertEquals('L: 1/8', $result['lines'][3]);
        $this->assertEquals('K: D', $result['lines'][4]);
    }

    public function testProcessStandardizesDirectiveSpacing() {
        $lines = [
            '%%pagewidth 8.5in',
            '%%leftmargin 1cm',
            '%%landscape',
            '%%MIDI program 1'
        ];

        $result = $this->pass->process($lines);

        // Formatting directives should have single space after %%
        $this->assertEquals('%% pagewidth 8.5in', $result['lines'][0]);
        $this->assertEquals('%% leftmargin 1cm', $result['lines'][1]);
        $this->assertEquals('%% landscape', $result['lines'][2]);
        // MIDI directives should be unchanged
        $this->assertEquals('%%MIDI program 1', $result['lines'][3]);
    }

    public function testProcessStandardizesVoiceSpacing() {
        $lines = [
            'V:Bagpipes',
            'V:Flute name="Flute"',
            'V:Drums'
        ];

        $result = $this->pass->process($lines);

        // Voice lines should have space after V:
        $this->assertEquals('V: Bagpipes', $result['lines'][0]);
        $this->assertEquals('V: Flute name="Flute"', $result['lines'][1]);
        $this->assertEquals('V: Drums', $result['lines'][2]);
    }

    public function testProcessStandardizesLyricsSpacing() {
        $lines = [
            'w:dar dod hid dar',
            'w:old lyrics here'
        ];

        $result = $this->pass->process($lines);

        // Lyrics lines should have space after w:
        $this->assertEquals('w: dar dod hid dar', $result['lines'][0]);
        $this->assertEquals('w: old lyrics here', $result['lines'][1]);
    }

    public function testProcessPreservesMusicLines() {
        $lines = [
            'A B C D',
            '| A B | C D |',
            '%% pagewidth 8.5in',
            'V: Bagpipes',
            'w: dar dod hid dar'
        ];

        $result = $this->pass->process($lines);

        // Music lines should be unchanged
        $this->assertEquals('A B C D', $result['lines'][0]);
        $this->assertEquals('| A B | C D |', $result['lines'][1]);
        // Other lines should be standardized
        $this->assertEquals('%% pagewidth 8.5in', $result['lines'][2]);
        $this->assertEquals('V: Bagpipes', $result['lines'][3]);
        $this->assertEquals('w: dar dod hid dar', $result['lines'][4]);
    }

    public function testProcessHandlesEmptyLines() {
        $lines = [
            'X: 1',
            '',
            'T: Test Tune',
            '',
            'A B C'
        ];

        $result = $this->pass->process($lines);

        // Empty lines should be preserved
        $this->assertCount(5, $result['lines']);
        $this->assertEquals('X: 1', $result['lines'][0]);
        $this->assertEquals('', $result['lines'][1]);
        $this->assertEquals('T: Test Tune', $result['lines'][2]);
        $this->assertEquals('', $result['lines'][3]);
        $this->assertEquals('A B C', $result['lines'][4]);
    }

    public function testProcessHandlesComments() {
        $lines = [
            '% This is a comment',
            'X:1',
            '% Another comment'
        ];

        $result = $this->pass->process($lines);

        // Comments should be unchanged
        $this->assertEquals('% This is a comment', $result['lines'][0]);
        $this->assertEquals('X: 1', $result['lines'][1]);
        $this->assertEquals('% Another comment', $result['lines'][2]);
    }
}