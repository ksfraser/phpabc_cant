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
    public function testMultiSongParsingAndValidation() {
        $dict = ['cannt' => 1];
        $abc = "X:1\nT:First Tune\nM:4/4\nL:1/4\nV:Bagpipes\n|A B C D|A2 B2|A B|\n\nX:2\nT:Second Tune\nM:2/4\nL:1/8\nV:Bagpipes\n|A B|A2|A|";
        $parser = new \Ksfraser\PhpabcCanntaireachd\AbcFileParser();
        $tunes = $parser->parse($abc);
        $this->assertCount(2, $tunes, 'Should parse two tunes');
        $this->assertEquals('First Tune', $tunes[0]->getHeaders()['T']);
        $this->assertEquals('Second Tune', $tunes[1]->getHeaders()['T']);
        // Assert V: lines are present in output for both tunes
        foreach ($tunes as $tune) {
            $out = $tune->render();
            $this->assertStringContainsString('V:Bagpipes', $out, 'V:Bagpipes header missing in output');
        }
        // Style check: 4/4 tune should have 8 bars (will fail, only 3 here)
        $checker = new \Ksfraser\PhpabcCanntaireachd\AbcSanityChecker();
        $issues = $checker->checkBagpipeStyle($tunes[0]);
        $this->assertNotEmpty($issues, 'Should detect style issues in first tune');

    }
    public function testVoiceOutputStyles() {
        $voiceBars = [
            'Bagpipes' => ['A B C D', 'A2 B2', 'A B', 'B2 A2', 'C D E F', 'G A B C', 'D E F G', 'A B C D'],
            'Drums' => ['z4', 'z4', 'z4', 'z4', 'z4', 'z4', 'z4', 'z4']
        ];
        $config = new \Ksfraser\PhpabcCanntaireachd\AbcProcessorConfig();
        $config->voiceOutputStyle = 'grouped';
        $config->barsPerLine = 4;
        $grouped = \Ksfraser\PhpabcCanntaireachd\AbcProcessor::renderVoices($voiceBars, $config);
        $this->assertStringContainsString('V:Bagpipes', implode(' ', $grouped));
        $this->assertStringContainsString('V:Drums', implode(' ', $grouped));
        $config->voiceOutputStyle = 'interleaved';
        $config->interleaveBars = 2;
        $interleaved = \Ksfraser\PhpabcCanntaireachd\AbcProcessor::renderVoices($voiceBars, $config);
        $this->assertStringContainsString('V:Bagpipes', implode(' ', $interleaved));
        $this->assertStringContainsString('V:Drums', implode(' ', $interleaved));
        $this->assertGreaterThan(1, count($interleaved), 'Should have multiple lines for interleaved output');
    }
    public function testHeaderFieldClasses() {
        $t = new \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderT('Title');
        $this->assertEquals('Title', $t->get());
        $this->assertEquals("T:Title\n", $t->render());
        $b = new \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderB();
        $b->add('Book1');
        $b->add('Book2');
        $this->assertEquals(['Book1','Book2'], $b->get());
        $this->assertStringContainsString('B:Book1', $b->render());
        $this->assertStringContainsString('B:Book2', $b->render());
    }
    public function testSingleHeaderPolicyFirstOrLast() {
        $abc = "X:1\nT:First\nT:Second\nM:4/4\nM:2/4\n";
        $parserFirst = new \Ksfraser\PhpabcCanntaireachd\AbcFileParser(['singleHeaderPolicy'=>'first']);
        $parserLast = new \Ksfraser\PhpabcCanntaireachd\AbcFileParser(['singleHeaderPolicy'=>'last']);
        $tuneFirst = $parserFirst->parse($abc)[0];
        $tuneLast = $parserLast->parse($abc)[0];
        $this->assertEquals('First', $tuneFirst->getHeaders()['T']->get());
        $this->assertEquals('Second', $tuneLast->getHeaders()['T']->get());
        $this->assertEquals('4/4', $tuneFirst->getHeaders()['M']->get());
        $this->assertEquals('2/4', $tuneLast->getHeaders()['M']->get());
    }
    public function testMissingHeaderRendersEmpty() {
        $tune = new \Ksfraser\PhpabcCanntaireachd\AbcTune();
        $out = $tune->render();
        $this->assertStringContainsString('T:', $out);
        $this->assertStringContainsString('M:', $out);
        $this->assertStringContainsString('L:', $out);
    }
    public function testMultiVoiceOutput() {
        $abc = "X:1\nT:Multi Voice\nM:2/4\nL:1/8\nV:Melody\n|A B|A2|A|\nV:Guitar\n|A B|A2|A|";
        $parser = new \Ksfraser\PhpabcCanntaireachd\AbcFileParser();
        $tunes = $parser->parse($abc);
        $this->assertCount(1, $tunes, 'Should parse one tune');
        // Test grouped output
        $tunes[0]->config = (object)['voiceOutputStyle'=>'grouped'];
        $grouped = $tunes[0]->render();
        $this->assertStringContainsString('V:Melody', $grouped, 'V:Melody header missing in grouped output');
        $this->assertStringContainsString('V:Guitar', $grouped, 'V:Guitar header missing in grouped output');
        $this->assertStringContainsString('|A B|A2|A|', $grouped, 'Bar lines missing in grouped output');
        // Test interleaved output
        $tunes[0]->config = (object)['voiceOutputStyle'=>'interleaved'];
        $interleaved = $tunes[0]->render();
        $this->assertStringContainsString('V:Melody', $interleaved, 'V:Melody header missing in interleaved output');
        $this->assertStringContainsString('V:Guitar', $interleaved, 'V:Guitar header missing in interleaved output');
        $this->assertStringContainsString('|A B|A2|A|', $interleaved, 'Bar lines missing in interleaved output');
    }
}
