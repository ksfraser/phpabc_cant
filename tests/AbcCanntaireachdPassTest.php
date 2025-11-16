<?php
use Ksfraser\PhpabcCanntaireachd\AbcCanntaireachdPass;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;
use PHPUnit\Framework\TestCase;

class AbcCanntaireachdPassTest extends TestCase
{
    private $dict;
    private $pass;

    protected function setUp(): void
    {
        $this->dict = new TokenDictionary();
        // Add some test mappings
        $this->dict->prepopulate([
            'A' => ['cannt_token' => 'en', 'bmw_token' => null, 'description' => 'Test A'],
            'B' => ['cannt_token' => 'o', 'bmw_token' => null, 'description' => 'Test B'],
            'C' => ['cannt_token' => 'o', 'bmw_token' => null, 'description' => 'Test C'],
            'D' => ['cannt_token' => 'a', 'bmw_token' => null, 'description' => 'Test D']
        ]);
        $this->pass = new AbcCanntaireachdPass($this->dict);
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf(AbcCanntaireachdPass::class, $this->pass);
    }

<<<<<<< HEAD
    public function testProcessEmptyLines()
    {
        $result = $this->pass->process([]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('lines', $result);
        $this->assertArrayHasKey('canntDiff', $result);
        $this->assertEmpty($result['lines']);
        $this->assertIsArray($result['canntDiff']);
    }

    public function testProcessNonBagpipeVoices()
    {
        $lines = [
            'X:1',
            'T:Test Tune',
            'V:Flute',
            'A B C D',
            'V:Drums',
            'A B C D'
        ];

        $result = $this->pass->process($lines);

        // Should not add any w: lines since no Bagpipes voice
        $this->assertCount(6, $result['lines']);
        foreach ($result['lines'] as $line) {
            $this->assertStringStartsNotWith('w:', trim($line));
        }
    }

    public function testProcessBagpipeVoiceWithExistingLyrics()
    {
        $lines = [
            'X:1',
            'T:Test Tune',
            'V:Bagpipes',
            'w: existing lyrics',
            'A B C D'
        ];

        $result = $this->pass->process($lines);

        // Should preserve existing lyrics and not generate new ones
        $this->assertCount(6, $result['lines']);
        $this->assertEquals('w: existing lyrics', $result['lines'][3]);
        $this->assertEquals('A B C D', $result['lines'][4]);
        $this->assertEquals('w: dar dod hid dar', $result['lines'][5]);
    }

    public function testProcessBagpipeVoiceWithoutLyrics()
    {
        $lines = [
            'X:1',
            'T:Test Tune',
            'V:Bagpipes',
            'A B C D'
        ];

        $result = $this->pass->process($lines);

        // Should add w: line with generated canntaireachd
        $this->assertCount(5, $result['lines']);
        $this->assertEquals('V:Bagpipes', $result['lines'][2]);
        $this->assertEquals('A B C D', $result['lines'][3]);
        $this->assertEquals('w: dar dod hid dar', $result['lines'][4]);
    }

    public function testProcessMultipleVoicesWithBagpipes()
    {
        $lines = [
            'X:1',
            'T:Test Tune',
            'V:Flute',
            'A B C D',
            'V:Bagpipes',
            'A B C D',
            'V:Drums',
            'A B C D'
        ];

        $result = $this->pass->process($lines);

    // Should add w: line only for Bagpipes voice
    $this->assertCount(9, $result['lines']);
    $this->assertEquals('V:Flute', $result['lines'][2]);
    $this->assertEquals('A B C D', $result['lines'][3]);
    $this->assertEquals('V:Bagpipes', $result['lines'][4]);
    $this->assertEquals('A B C D', $result['lines'][5]);
    $this->assertEquals('w: dar dod hid dar', $result['lines'][6]);
    $this->assertEquals('V:Drums', $result['lines'][7]);
    $this->assertEquals('A B C D', $result['lines'][8]);
    }

    public function testProcessBagpipeVoiceWithTimingErrors()
    {
        $lines = [
            'X:1',
            'T:Test Tune',
            'V:Bagpipes',
            'A B C D | A B |TIMING ERROR|'
        ];

        $result = $this->pass->process($lines);

    // Should not add w: line for timing error lines
    $this->assertCount(5, $result['lines']);
    $this->assertEquals('V:Bagpipes', $result['lines'][2]);
    $this->assertEquals('A B C D | A B |TIMING ERROR|', $result['lines'][3]);
    $this->assertStringStartsWith('w: ', $result['lines'][4]);
    }

=======
>>>>>>> 4113fb97ff103f0af8d41462ff6994831d290ccf
    public function testProcessCommentsAndEmptyLines()
    {
    $abcText = "X:1\nT:Test Tune\nV:Bagpipes\nK:HP\n% This is a comment\n\nA B C D";
        $tuneClass = \Ksfraser\PhpabcCanntaireachd\Tune\AbcTune::class;
        $tune = $tuneClass::parse($abcText);
        // Force Bagpipes voice to be BagpipeVoice
        $voices = $tune->getVoices();
        if (!($voices['Bagpipes'] ?? null) instanceof \Ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice) {
            $tune->getVoices()['Bagpipes'] = new \Ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice('Bagpipes', 'Bagpipes', '');
        }
        $this->pass->process($tune);
        $barsBagpipes = $tune->getVoiceBars()['Bagpipes'] ?? [];
        fwrite(STDERR, "Bagpipes bars: ".print_r($barsBagpipes, true));
        $found = [];
        foreach ($barsBagpipes as $bar) {
            fwrite(STDERR, "Bar notes: ".print_r($bar->notes, true));
            foreach ($bar->notes as $i => $note) {
                fwrite(STDERR, "Note $i: ".print_r($note, true));
                $c = $note->getCanntaireachd();
                if ($c !== null && $c !== '') {
                    $found[] = $c;
                }
            }
        }
        fwrite(STDERR, "Found canntaireachd: ".print_r($found, true));
        $this->assertEquals(['dar','dod','hid','dar'], $found);
    }

    public function testProcessMultipleTunes()
    {
<<<<<<< HEAD
        $lines = [
            'X:1',
            'T:First Tune',
            'V:Bagpipes',
            'A B C D',
            '',
            'X:2',
            'T:Second Tune',
            'V:Bagpipes',
            'D C B A'
        ];

        $result = $this->pass->process($lines);

    // Should add w: lines for both tunes
    $this->assertCount(11, $result['lines']);
    // First tune
    $this->assertEquals('A B C D', $result['lines'][3]);
    $this->assertEquals('w: dar dod hid dar', $result['lines'][4]);
    // Second tune
    $this->assertEquals('D C B A', $result['lines'][9]);
    $this->assertEquals('w: dar hid dod dar', $result['lines'][10]);
=======
    $abcText = "X:1\nT:First Tune\nV:Bagpipes\nK:HP\nA B C D\n\nX:2\nT:Second Tune\nV:Bagpipes\nK:HP\nD C B A";
        $tuneClass = \Ksfraser\PhpabcCanntaireachd\Tune\AbcTune::class;
        $tune = $tuneClass::parse($abcText);
        // Force Bagpipes voice to be BagpipeVoice
        $voices = $tune->getVoices();
        if (!($voices['Bagpipes'] ?? null) instanceof \Ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice) {
            $tune->getVoices()['Bagpipes'] = new \Ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice('Bagpipes', 'Bagpipes', '');
        }
        $this->pass->process($tune);
        $bars = $tune->getVoiceBars()['Bagpipes'] ?? [];
        fwrite(STDERR, "Bagpipes bars: ".print_r($bars, true));
        $canntaireachd = [];
        foreach ($bars as $bar) {
            fwrite(STDERR, "Bar notes: ".print_r($bar->notes, true));
            foreach ($bar->notes as $note) {
                fwrite(STDERR, "Note: ".print_r($note, true));
                $canntaireachd[] = $note->getCanntaireachd();
            }
        }
        fwrite(STDERR, "Found canntaireachd: ".print_r($canntaireachd, true));
        // First tune: A B C D, Second tune: D C B A
        $this->assertEquals(['dar','dod','hid','dar','dar','hid','dod','dar'], $canntaireachd);
>>>>>>> 4113fb97ff103f0af8d41462ff6994831d290ccf
    }


    public function testNoteLevelCanntaireachdAssignment()
    {
        // Simulate a parsed tune with Bagpipe and Flute voices
    $abcText = "X:1\nT:Note Level Test\nV:Bagpipes\nK:HP\nA B C D\nV:Flute\nK:HP\nA B C D";
        $tuneClass = \Ksfraser\PhpabcCanntaireachd\Tune\AbcTune::class;
        $tune = $tuneClass::parse($abcText);
        // Force Bagpipes voice to be BagpipeVoice
        $voices = $tune->getVoices();
        if (!($voices['Bagpipes'] ?? null) instanceof \Ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice) {
            $tune->getVoices()['Bagpipes'] = new \Ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice('Bagpipes', 'Bagpipes', '');
        }
        $this->pass->process($tune);
        $barsBagpipes = $tune->getVoiceBars()['Bagpipes'] ?? [];
        $barsFlute = $tune->getVoiceBars()['Flute'] ?? [];
        fwrite(STDERR, "Bagpipes bars: ".print_r($barsBagpipes, true));
        fwrite(STDERR, "Flute bars: ".print_r($barsFlute, true));
        // BagpipeVoice: each note should have canntaireachd
        foreach ($barsBagpipes as $bar) {
            fwrite(STDERR, "Bar notes: ".print_r($bar->notes, true));
            foreach ($bar->notes as $i => $note) {
                fwrite(STDERR, "Bagpipe note $i: ".print_r($note, true));
                $this->assertEquals(['dar','dod','hid','dar'][$i], $note->getCanntaireachd(), "Bagpipe note $i should have correct canntaireachd");
            }
        }
        // Flute: each note should have null or empty canntaireachd
        foreach ($barsFlute as $bar) {
            fwrite(STDERR, "Bar notes: ".print_r($bar->notes, true));
            foreach ($bar->notes as $note) {
                fwrite(STDERR, "Flute note: ".print_r($note, true));
                $this->assertTrue($note->getCanntaireachd() === null || $note->getCanntaireachd() === '', "Non-bagpipe note should have null/empty canntaireachd");
            }
        }
    }

    public function testInvalidAbcTokensFallback()
    {
        $lines = [
            'V:Bagpipes',
            'A X B'  // X is not in dictionary
        ];

        $result = $this->pass->process($lines);

        // Should fallback to [X] for unmappable token
        $this->assertCount(3, $result['lines']);
        $this->assertEquals('A X B', $result['lines'][1]);
        $this->assertEquals('w: dar [X] dod', $result['lines'][2]);
    }

    public function testEmptyMusicLine()
    {
        $lines = [
            'V:Bagpipes',
            ''  // Empty line
        ];

        $result = $this->pass->process($lines);

        // Should not add w: for empty line
        $this->assertCount(2, $result['lines']);
        $this->assertEquals('V:Bagpipes', $result['lines'][0]);
        $this->assertEquals('', $result['lines'][1]);
    }

    public function testMalformedMusicLine()
    {
        $lines = [
            'V:Bagpipes',
            '|||'  // Only bars, no notes
        ];

        $result = $this->pass->process($lines);

        // Should not add w: for line with no notes
        $this->assertCount(2, $result['lines']);
        $this->assertEquals('V:Bagpipes', $result['lines'][0]);
        $this->assertEquals('|||', $result['lines'][1]);
    }

    public function testLineWithOnlyRests()
    {
        $lines = [
            'V:Bagpipes',
            'z Z'  // Rests
        ];

        $result = $this->pass->process($lines);

        // Should handle rests if in dictionary, else fallback
        $this->assertCount(3, $result['lines']);
        $this->assertEquals('z Z', $result['lines'][1]);
        $this->assertStringStartsWith('w: ', $result['lines'][2]);
    }

    public function testMultiVoiceWithoutBagpipes()
    {
        $lines = [
            'V:Flute',
            'A B C',
            'V:Drums',
            'D E F'
        ];

        $result = $this->pass->process($lines);

        // Should not add any w: lines
        $this->assertCount(4, $result['lines']);
        foreach ($result['lines'] as $line) {
            $this->assertStringStartsNotWith('w:', trim($line));
        }
    }

    public function testBagpipesWithComplexTokens()
    {
        $lines = [
            'V:Bagpipes',
            'A, A B\' C##'  // Accidentals and octaves
        ];

        $result = $this->pass->process($lines);

        // Should handle accidentals/octaves, fallback if not exact match
        $this->assertCount(3, $result['lines']);
        $this->assertEquals('A, A B\' C##', $result['lines'][1]);
        $this->assertStringStartsWith('w: ', $result['lines'][2]);
    }

    public function testNonMusicLineInBagpipes()
    {
        $lines = [
            'V:Bagpipes',
            'M:4/4',  // Header line
            'A B C'
        ];

        $result = $this->pass->process($lines);

        // Should not treat header as music, add w: only for music line
        $this->assertCount(4, $result['lines']);
        $this->assertEquals('M:4/4', $result['lines'][1]);
        $this->assertEquals('A B C', $result['lines'][2]);
        $this->assertEquals('w: dar dod hid', $result['lines'][3]);
    }

    public function testBagpipesWithTimingErrors()
    {
        $lines = [
            'V:Bagpipes',
            'A B C |TIMING ERROR|'
        ];

        $result = $this->pass->process($lines);

        // Should still add w: but log timing issues
        $this->assertCount(3, $result['lines']);
        $this->assertEquals('A B C |TIMING ERROR|', $result['lines'][1]);
        $this->assertStringStartsWith('w: ', $result['lines'][2]);
    }
}
