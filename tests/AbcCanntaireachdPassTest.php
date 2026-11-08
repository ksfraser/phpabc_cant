<?php
use ksfraser\PhpabcCanntaireachd\AbcCanntaireachdPass;
use ksfraser\PhpabcCanntaireachd\TokenDictionary;
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

    public function testProcessCommentsAndEmptyLines()
    {
    $abcText = "X:1\nT:Test Tune\nV:Bagpipes\nK:HP\n% This is a comment\n\nA B C D";
        $tuneClass = \ksfraser\PhpabcCanntaireachd\Tune\AbcTune::class;
        $tune = $tuneClass::parse($abcText);
        // Force Bagpipes voice to be BagpipeVoice
        $voices = $tune->getVoices();
        if (!($voices['Bagpipes'] ?? null) instanceof \ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice) {
            $tune->getVoices()['Bagpipes'] = new \ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice('Bagpipes', 'Bagpipes', '');
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
        $this->assertEquals(['en','o','o','a'], $found);
    }

    public function testProcessMultipleTunes()
    {
    $abcText = "X:1\nT:First Tune\nV:Bagpipes\nK:HP\nA B C D\n\nX:2\nT:Second Tune\nV:Bagpipes\nK:HP\nD C B A";
        $tuneClass = \ksfraser\PhpabcCanntaireachd\Tune\AbcTune::class;
        $tune = $tuneClass::parse($abcText);
        // Force Bagpipes voice to be BagpipeVoice
        $voices = $tune->getVoices();
        if (!($voices['Bagpipes'] ?? null) instanceof \ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice) {
            $tune->getVoices()['Bagpipes'] = new \ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice('Bagpipes', 'Bagpipes', '');
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
        $this->assertEquals(['en','o','o','a','a','o','o','en'], $canntaireachd);
    }


    public function testNoteLevelCanntaireachdAssignment()
    {
        // Simulate a parsed tune with Bagpipe and Flute voices
    $abcText = "X:1\nT:Note Level Test\nV:Bagpipes\nK:HP\nA B C D\nV:Flute\nK:HP\nA B C D";
        $tuneClass = \ksfraser\PhpabcCanntaireachd\Tune\AbcTune::class;
        $tune = $tuneClass::parse($abcText);
        // Force Bagpipes voice to be BagpipeVoice
        $voices = $tune->getVoices();
        if (!($voices['Bagpipes'] ?? null) instanceof \ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice) {
            $tune->getVoices()['Bagpipes'] = new \ksfraser\PhpabcCanntaireachd\Voices\BagpipeVoice('Bagpipes', 'Bagpipes', '');
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
        $this->assertEquals('w: en [X] o', $result['lines'][2]);
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
        $this->assertEquals('w: en o o', $result['lines'][3]);
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

    public function testPipeBandSnareConvertsTwoNotesToCD()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, true);
        $lines = [
            'V:Snare',
            'A B'
        ];

        $result = $pass->process($lines);

        // Two distinct pitches: A (lower) → C (Left/la), B (higher) → D (Right/ra)
        // Original snare voice preserved + new V:Drumline voice created
        $this->assertCount(5, $result['lines']);
        $this->assertEquals('V:Snare', $result['lines'][0]);
        $this->assertEquals('A B', $result['lines'][1]);
        $this->assertStringContainsString('V:Drumline', $result['lines'][2]);
        $this->assertStringContainsString('| C D |', $result['lines'][3]);
        $this->assertStringContainsString('w: la ra', $result['lines'][4]);
    }

    public function testPipeBandSnareOnlyTwoNotesAllowed()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, true);
        $lines = [
            'V:Snare',
            'A B C'  // Three distinct pitches - should not convert
        ];

        $result = $pass->process($lines);

        // Should leave unchanged when not exactly 2 pitches
        $this->assertCount(2, $result['lines']);
        $this->assertEquals('V:Snare', $result['lines'][0]);
        $this->assertEquals('A B C', $result['lines'][1]);
    }

    public function testPipeBandSnareHigherPitchBecomesD()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, true);
        // Lowercase notes are higher pitch than uppercase
        $lines = [
            'V:Snare name="Snare Drum"',
            'A b'
        ];

        $result = $pass->process($lines);

        // A is lower → C (Left), b is higher → D (Right)
        $this->assertCount(5, $result['lines']);
        $this->assertEquals('V:Snare name="Snare Drum"', $result['lines'][0]);
        $this->assertEquals('A b', $result['lines'][1]);
        $this->assertStringContainsString('V:Drumline', $result['lines'][2]);
        $this->assertStringContainsString('| C D |', $result['lines'][3]);
        $this->assertStringContainsString('w: la ra', $result['lines'][4]);
    }

    public function testPipeBandSnareNonSnareVoiceUnaffected()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, true);
        $lines = [
            'V:Bagpipes',
            'A B'
        ];

        $result = $pass->process($lines);

        // Bagpipes voice should not be converted to C/D
        $this->assertEquals('A B', $result['lines'][1]);
        $this->assertStringStartsWith('w: ', $result['lines'][2]);
    }

    public function testKnockOnAddsRestsForAllVoices()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, false, true);
        $lines = [
            'X:1',
            'V:Bagpipes',
            'A B C',
        ];

        $result = $pass->process($lines);

        // Should have knock-on rest lines before the original voice header
        $this->assertStringContainsString('%% Knock-on attack roll intro', $result['lines'][0]);
        $this->assertStringContainsString('V:Bagpipes [M:2/4][L:1/8]', $result['lines'][1]);
        $this->assertStringContainsString('z2 z2', $result['lines'][2]);
        $this->assertStringContainsString('%% End knock-on', $result['lines'][3]);
    }

    public function testKnockOnSnareUsesCDWhenPipeBandSnare()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, true, true);
        $lines = [
            'X:1',
            'V:Snare',
            'A B',
        ];

        $result = $pass->process($lines);

        // Snare knock-on should use C/D drumline notation when pipebandsnare is active
        $knockOnLines = array_values(array_filter($result['lines'], fn ($l) => str_contains($l, '!fff!')));
        $this->assertNotEmpty($knockOnLines);
        $this->assertStringContainsString('C C', $knockOnLines[0]);
    }

    public function testKnockOnSnareOriginalNotationWithoutPipeBandSnare()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, false, true);
        $lines = [
            'X:1',
            'V:Snare',
            'A B',
        ];

        $result = $pass->process($lines);

        // Snare knock-on should use original notation without pipebandsnare
        $knockOnLines = array_values(array_filter($result['lines'], fn ($l) => str_contains($l, '!fff!')));
        $this->assertNotEmpty($knockOnLines);
        $this->assertStringContainsString('c2 c2', $knockOnLines[0]);
    }

    public function testKnockOnUsesL16WhenDeclared()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, false, true);
        $lines = [
            'X:1',
            'L:1/16',
            'V:Bagpipes',
            'A B C',
        ];

        $result = $pass->process($lines);

        // With L:1/16, quarter note = z4, bar = z4 z4
        $this->assertStringContainsString('z4 z4', $result['lines'][2]);
        $this->assertStringContainsString('L:1/16', $result['lines'][1]);
    }

    public function testVoiceLinesPrefixesMusicLine()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, false, false, true);
        $lines = [
            'X:1',
            'V:Snare',
            'M:2/4',
            'L:1/8',
            'A B',
        ];

        $result = $pass->process($lines);

        // Music line should be prefixed with voice/time/unit and bar markers
        $musicLine = end($result['lines']);
        $this->assertStringContainsString('[V:Snare][M:2/4][L:1/8]', $musicLine);
        $this->assertStringContainsString('| A B |', $musicLine);
    }

    public function testVoiceLinesAnacrusisUsesPipeBar()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, false, false, true);
        $lines = [
            'X:1',
            'V:Bagpipes',
            'M:2/4',
            'L:1/8',
            'A B',        // first music line → anacrusis | marker
            'C D',        // second line → x| marker
        ];

        $result = $pass->process($lines);

        // First music line after V: header gets | (anacrusis), next gets x|
        $musicLines = array_values(array_filter($result['lines'], fn ($l) => str_contains($l, '| A B |')));
        $this->assertNotEmpty($musicLines);
        $this->assertStringContainsString('| A B |', $musicLines[0]);

        $musicLines2 = array_values(array_filter($result['lines'], fn ($l) => str_contains($l, 'x| C D |')));
        $this->assertNotEmpty($musicLines2);
        $this->assertStringContainsString('x| C D |', $musicLines2[0]);
    }

    public function testPipeBandSnareSnarePlusDrumlineReplacesDrumline()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, true);
        $lines = [
            'X:1',
            'V:Snare',
            'M:2/4',
            'L:1/8',
            'A B',
            'V:Drumline',
            'M:2/4',
            'L:1/8',
            'z2 z2',
        ];

        $result = $pass->process($lines);

        // Original snare voice preserved
        $this->assertStringContainsString('V:Snare', $result['lines'][1]);
        $this->assertStringContainsString('A B', $result['lines'][4]);

        // Drumline voice replaced with converted snare (C/D)
        $drumlineLines = array_values(array_filter($result['lines'], fn ($l) => str_contains($l, 'V:Drumline')));
        $this->assertNotEmpty($drumlineLines);
        $this->assertStringContainsString('[M:2/4][L:1/8]', $drumlineLines[0]);

        // Converted drumline music uses C/D
        $convLines = array_values(array_filter($result['lines'], fn ($l) => str_contains($l, '| C D |')));
        $this->assertNotEmpty($convLines);

        // Original drumline content (z2 z2) should NOT appear
        $this->assertStringNotContainsString('z2 z2', implode("\n", $result['lines']));
    }

    public function testPipeBandSnareDrumlineOnlyCreatesSnareReverse()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, true);
        $lines = [
            'X:1',
            'V:Drumline',
            'M:2/4',
            'L:1/8',
            'C D',
        ];

        $result = $pass->process($lines);

        // Original drumline voice preserved
        $this->assertStringContainsString('V:Drumline', $result['lines'][1]);

        // New V:Snare voice created with reverse-converted content (C/D → A/c)
        $snareLines = array_values(array_filter($result['lines'], fn ($l) => str_contains($l, 'V:Snare')));
        $this->assertNotEmpty($snareLines);

        // Reverse-converted music should have A/c instead of C/D
        $convLines = array_values(array_filter($result['lines'], fn ($l) => str_contains($l, '| A c |') || str_contains($l, '| A c|')));
        $this->assertNotEmpty($convLines, 'Expected reverse-converted line with A c, got: ' . implode("\n", $result['lines']));
    }

    public function testPipeBandSnareThreePitchesError()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, true);
        $lines = [
            'X:1',
            'V:Snare',
            'M:2/4',
            'L:1/8',
            'A B C',  // Three distinct pitches - should error
        ];

        $result = $pass->process($lines);

        // Should return lines unchanged (no conversion)
        $this->assertStringContainsString('V:Snare', $result['lines'][1]);
        $this->assertStringContainsString('A B C', $result['lines'][4]);

        // No V:Drumline voice should be created
        $this->assertStringNotContainsString('V:Drumline', implode("\n", $result['lines']));
    }

    public function testMusecoreAllNotesBecomeC()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, true, false, false, true);
        $lines = [
            'X:1',
            'V:Snare',
            'M:2/4',
            'L:1/8',
            'A B D',
        ];

        $result = $pass->process($lines);

        // Drumline voice created with all notes as C
        $drumLines = array_values(array_filter($result['lines'], fn ($l) => str_contains($l, 'V:Drumline')));
        $this->assertNotEmpty($drumLines);

        // All notes in drumline voice should be C, no D
        $drumlineMusicLines = array_values(array_filter($result['lines'], fn ($l) =>
            str_starts_with(trim($l), '|') && str_contains($l, 'C') && !str_contains($l, 'D')
        ));
        $this->assertNotEmpty($drumlineMusicLines);
    }

    public function testMusecoreFlamsAndDragsBecomeV()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, true, false, false, true);
        $lines = [
            'X:1',
            'V:Snare',
            'M:2/4',
            'L:1/8',
            '{C}A {CC}B',
        ];

        $result = $pass->process($lines);

        // Flams {C} and drags {CC} should become !accent!
        $drumlineMusicLines = array_values(array_filter($result['lines'], fn ($l) => str_contains($l, '!accent!')));
        $this->assertNotEmpty($drumlineMusicLines, 'Expected !accent! in drumline, got: ' . implode("\n", $result['lines']));
        $this->assertStringNotContainsString('{C}', implode("\n", $drumlineMusicLines));
        $this->assertStringNotContainsString('{CC}', implode("\n", $drumlineMusicLines));
    }

    public function testMusecoreRollsBecomeR()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, true, false, false, true);
        $lines = [
            'X:1',
            'V:Snare',
            'M:2/4',
            'L:1/8',
            'r',
        ];

        $result = $pass->process($lines);

        // Eighth note roll r → (R.
        $drumlineMusicLines = array_values(array_filter($result['lines'], fn ($l) => str_contains($l, '(R.')));
        $this->assertNotEmpty($drumlineMusicLines, 'Expected (R. in drumline, got: ' . implode("\n", $result['lines']));
    }

    public function testMusecoreQuarterRollSubdivides()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, true, false, false, true);
        $lines = [
            'X:1',
            'V:Snare',
            'M:2/4',
            'L:1/8',
            'C2',
        ];

        $result = $pass->process($lines);

        // Quarter note C2 → CC (2x eighths) in drumline voice
        $drumlineMusicLines = array_values(array_filter($result['lines'], fn ($l) => str_contains($l, 'CC')));
        $this->assertNotEmpty($drumlineMusicLines, 'Expected CC in drumline, got: ' . implode("\n", $result['lines']));
        // Original C2 may still appear in snare voice, but drumline should have CC
        $this->assertStringContainsString('| CC |', implode("\n", $drumlineMusicLines));
    }

    public function testMusecoreLargerThanQuarterSubdivides()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, true, false, false, true);
        $lines = [
            'X:1',
            'V:Snare',
            'M:2/4',
            'L:1/8',
            'C4',
        ];

        $result = $pass->process($lines);

        // Half note C4 → CCCC (subdivide to eighths) in drumline voice
        $drumlineMusicLines = array_values(array_filter($result['lines'], fn ($l) => str_contains($l, 'CCCC')));
        $this->assertNotEmpty($drumlineMusicLines, 'Expected CCCC in drumline, got: ' . implode("\n", $result['lines']));
        $this->assertStringContainsString('| CCCC |', implode("\n", $drumlineMusicLines));
    }

    public function testMusecoreCanntAllLa()
    {
        $pass = new AbcCanntaireachdPass($this->dict, null, true, false, false, true);
        $lines = [
            'X:1',
            'V:Snare',
            'M:2/4',
            'L:1/8',
            'C C C',
        ];

        $result = $pass->process($lines);

        // All C notes → la canntaireachd
        $wLines = array_values(array_filter($result['lines'], fn ($l) => str_starts_with($l, 'w: ')));
        $this->assertNotEmpty($wLines);
        $this->assertStringContainsString('la la la', $wLines[0]);
    }
}
