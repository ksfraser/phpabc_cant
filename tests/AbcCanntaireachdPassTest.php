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
            'A' => ['cannt_token' => 'dar', 'bmw_token' => null, 'description' => 'Test A'],
            'B' => ['cannt_token' => 'dod', 'bmw_token' => null, 'description' => 'Test B'],
            'C' => ['cannt_token' => 'hid', 'bmw_token' => null, 'description' => 'Test C'],
            'D' => ['cannt_token' => 'dar', 'bmw_token' => null, 'description' => 'Test D']
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
}
