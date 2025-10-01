<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Tune\MelodyToBagpipesCopier;

class MelodyToBagpipesCopierTest extends TestCase
{
    public function testNoMelodyReturnsOriginal()
    {
        $lines = [
            'V:Drums name="Drums"',
            '[V:Drums] z4 | z4 |',
        ];
        $copier = new MelodyToBagpipesCopier();
        $result = $copier->copy($lines);
        $this->assertEquals($lines, $result);
    }

    public function testMelodyCreatesBagpipes()
    {
        $lines = [
            'V:M name="Melody"',
            '[V:M] A4 | B4 |',
        ];
        $copier = new MelodyToBagpipesCopier();
        $result = $copier->copy($lines);
        $this->assertContains('V:Bagpipes name="Bagpipes" clef=treble octave=0', $result);
        $this->assertContains('[V:Bagpipes] A4 | B4 |', $result);
    }

    public function testExistingBagpipesNotDuplicated()
    {
        $lines = [
            'V:M name="Melody"',
            'V:Bagpipes name="Bagpipes" clef=treble octave=0',
            '[V:M] A4 | B4 |',
            '[V:Bagpipes] A4 | B4 |',
        ];
        $copier = new MelodyToBagpipesCopier();
        $result = $copier->copy($lines);
        $this->assertEquals($lines, $result);
    }
}
