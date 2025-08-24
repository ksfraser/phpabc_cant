<?php
use Ksfraser\PhpabcCanntaireachd\AbcProcessor;
use PHPUnit\Framework\TestCase;

class TestAbcProcessor extends TestCase {
    public function testVoiceReorderingDrumsLast() {
        $dict = ['cannt' => 1];
        $abc = "V:Flute\nV:Drums\nV:Bagpipes\n";
        $result = AbcProcessor::process($abc, $dict);
        $lines = $result['lines'];
        $this->assertEquals('V:Bagpipes', trim($lines[1]));
        $this->assertEquals('V:Drums', trim($lines[count($lines)-1]));
    }
    public function testLyricsToW() {
        $dict = ['cannt' => 1];
        $abc = "V:Bagpipes\nw:hello world\n";
        $result = AbcProcessor::process($abc, $dict);
        $this->assertStringContainsString('W: hello world', implode(' ', $result['lines']));
    }
    public function testCanntDiff() {
        $dict = ['cannt' => 1];
        $abc = "V:Bagpipes\n%canntaireachd: old\n";
        $result = AbcProcessor::process($abc, $dict);
        $this->assertNotEmpty($result['canntDiff']);
    }
    public function testTimingValidationMarksBarsAndLogsErrors() {
        $dict = ['cannt' => 1];
        // Bar 3 is intentionally short (should be 4 beats, only 1 note)
        $abc = "M:4/4\nL:1/4\nV:Bagpipes\n|A B C D|\n|A2 B2|\n|A|";
        $result = AbcProcessor::process($abc, $dict);
        $lines = $result['lines'];
        $errors = $result['errors'];
        $timingFound = false;
        foreach ($lines as $line) {
            if (strpos($line, 'TIMING') !== false) {
                $timingFound = true;
                break;
            }
        }
        $this->assertTrue($timingFound, 'TIMING marker not found in output lines');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('TIMING:', $errors[0]);
    }
}
