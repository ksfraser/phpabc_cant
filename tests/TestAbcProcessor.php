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
}
