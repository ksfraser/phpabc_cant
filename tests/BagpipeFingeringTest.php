<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Tests;

use ksfraser\PhpabcCanntaireachd\Bagpipe\BagpipeFingering;
use ksfraser\PhpabcCanntaireachd\Bagpipe\BagpipeFingeringWriter;
use ksfraser\PhpabcCanntaireachd\TokenDictionary;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ksfraser\PhpabcCanntaireachd\Bagpipe\BagpipeFingering
 * @covers \ksfraser\PhpabcCanntaireachd\Bagpipe\BagpipeFingeringWriter
 */
class BagpipeFingeringTest extends TestCase
{
    private BagpipeFingering $fingering;

    protected function setUp(): void
    {
        $this->fingering = new BagpipeFingering();
    }

    public function testTheScaleHasNineNotesFromTheLowGToTheHighA(): void
    {
        $this->assertSame(
            ['G', 'A', 'B', 'c', 'd', 'e', 'f', 'g', 'a'],
            $this->fingering->getScaleNotes()
        );
    }

    public function testTheLowGClosesEveryHole(): void
    {
        $this->assertSame(
            BagpipeFingering::HOLES,
            $this->fingering->getClosedHoles('G')
        );
    }

    public function testTheBottomHandPlaysTheLowNotes(): void
    {
        // The A: the right index, middle and ring down, the little finger up.
        $this->assertSame(
            ['thumb', 'top index', 'top middle', 'top ring', 'bottom index', 'bottom middle', 'bottom ring'],
            $this->fingering->getClosedHoles('A')
        );
        // The B: the right index and middle down.
        $this->assertSame(
            ['thumb', 'top index', 'top middle', 'top ring', 'bottom index', 'bottom middle'],
            $this->fingering->getClosedHoles('B')
        );
        // The C: the right index down, and the little finger down as well.
        $this->assertSame(
            ['thumb', 'top index', 'top middle', 'top ring', 'bottom index'],
            $this->fingering->getClosedHoles('c')
        );
        // The D: the little finger down, the other right hand fingers up.
        $this->assertSame(
            ['thumb', 'top index', 'top middle', 'top ring', 'pinky'],
            $this->fingering->getClosedHoles('d')
        );
    }

    public function testTheTopHandPlaysTheHighNotes(): void
    {
        // The E: the top hand's thumb, index and middle down, the ring up.
        $this->assertSame(
            ['thumb', 'top index', 'top middle', 'bottom index', 'bottom middle', 'bottom ring'],
            $this->fingering->getClosedHoles('e')
        );
        // The F: the top hand's thumb and index down.
        $this->assertSame(
            ['thumb', 'top index', 'bottom index', 'bottom middle', 'bottom ring'],
            $this->fingering->getClosedHoles('f')
        );
        // The high G: the thumb down, the top hand's three fingers up.
        $this->assertSame(
            ['thumb', 'bottom index', 'bottom middle', 'bottom ring'],
            $this->fingering->getClosedHoles('g')
        );
        // The high A: the thumb up, the top hand's ring finger down, so that
        // the left hand stays on the chanter.
        $this->assertSame(
            ['top ring', 'bottom index', 'bottom middle', 'bottom ring'],
            $this->fingering->getClosedHoles('a')
        );
    }

    public function testTheLowGHasEveryHoleClosedAndTheHighAHasTheThumbOpen(): void
    {
        // The low G is played with every hole closed, so the note sounds at
        // the vent; the high A has the thumb hole open, so it sounds there.
        $this->assertNotContains('thumb', $this->fingering->getClosedHoles('a'));
        $this->assertContains('thumb', $this->fingering->getClosedHoles('g'));
    }

    public function testEveryEmbellishmentHasAWordInTheDictionary(): void
    {
        $missing = [];
        foreach ($this->fingering->getEmbellishments() as $name => $embellishment) {
            if ($embellishment['word'] === '?') {
                $missing[$name] = $embellishment['token'];
            }
        }

        $this->assertSame([], $missing, 'every embellishment needs a word in the dictionary');
    }

    public function testTheWordsAreTheOnesInTheDictionary(): void
    {
        $dictionary = new TokenDictionary();
        $embellishments = $this->fingering->getEmbellishments($dictionary);

        $this->assertSame('hoto', $embellishments['doubling on B']['word']);
        $this->assertSame('rarin', $embellishments['birl']['word']);
        $this->assertSame('dada', $embellishments['grip on d']['word']);
        $this->assertSame('darida', $embellishments['taorluath on d']['word']);
    }

    public function testTheFormatFileHasADecorationForEachNoteOfTheScale(): void
    {
        $writer = new BagpipeFingeringWriter($this->fingering);
        $file = $writer->render();

        foreach ($this->fingering->getScaleNotes() as $note) {
            $name = $writer->decorationName($note);
            $this->assertStringContainsString('deco ' . $name, $file);
            $this->assertStringContainsString('/' . $name . '{', $file);
        }
    }

    public function testTheFormatFileFillsTheHolesThatAreClosed(): void
    {
        $writer = new BagpipeFingeringWriter($this->fingering);
        $file = $writer->render();

        // The low G closes every hole.
        $this->assertSame(
            8,
            substr_count($this->procedureOf($file, 'pipeG'), 'pipe_hole_closed')
        );
        // The A leaves the little finger hole open.
        $this->assertSame(
            7,
            substr_count($this->procedureOf($file, 'pipeA'), 'pipe_hole_closed')
        );
        // The high A leaves the thumb hole open.
        $this->assertSame(
            4,
            substr_count($this->procedureOf($file, 'pipea'), 'pipe_hole_closed')
        );
    }

    public function testTheFormatFileCarriesTheEmbellishmentWords(): void
    {
        $file = (new BagpipeFingeringWriter($this->fingering))->render();

        $this->assertStringContainsString('doubling on B', $file);
        $this->assertStringContainsString('hoto', $file);
        $this->assertStringContainsString('grip on d', $file);
        $this->assertStringContainsString('dada', $file);
    }

    /**
     * The body of one PostScript procedure.
     */
    private function procedureOf(string $file, string $name): string
    {
        $start = strpos($file, '/' . $name . '{');
        $this->assertNotFalse($start, 'the procedure ' . $name . ' is missing');
        $end = strpos($file, '}!', $start);
        $this->assertNotFalse($end);
        return substr($file, $start, $end - $start);
    }
}
