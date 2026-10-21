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

    public function testTheScaleRunsFromTheLowGToTheHighD(): void
    {
        $this->assertSame(
            ['G', 'A', 'B', 'c', 'd', 'e', 'f', 'g', 'a', 'b', "c'", "d'"],
            $this->fingering->getScaleNotes()
        );
    }

    public function testTheLowGClosesEveryHole(): void
    {
        $this->assertSame(
            ['1', '2', '3', '4', '5', '6', '7', '8'],
            $this->fingering->getClosedHoles('G')
        );
    }

    public function testTheHolesAreClosedOneAtATimeFromTheBottomUp(): void
    {
        // The A leaves the lower back hole open, the B leaves the left ring
        // finger up as well, and so on up to the D.
        $this->assertSame(['1', '2', '3', '4', '5', '6', '8'], $this->fingering->getClosedHoles('A'));
        $this->assertSame(['1', '2', '3', '4', '5', '8'], $this->fingering->getClosedHoles('B'));
        $this->assertSame(['1', '2', '3', '4', '8'], $this->fingering->getClosedHoles('c'));
        $this->assertSame(['1', '2', '3', '8'], $this->fingering->getClosedHoles('d'));
    }

    public function testAboveTheETheChanterIsOpen(): void
    {
        foreach (['e', 'g', 'a', 'b', "c'", "d'"] as $note) {
            $this->assertSame([], $this->fingering->getClosedHoles($note), $note);
        }
    }

    public function testTheFIsACrossFingering(): void
    {
        // The F is played with the right index finger down and the rest open.
        $this->assertSame(['1'], $this->fingering->getClosedHoles('f'));
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
        // The E is open all the way.
        $this->assertSame(
            0,
            substr_count($this->procedureOf($file, 'pipee'), 'pipe_hole_closed')
        );
        // The F is a cross fingering: one hole down.
        $this->assertSame(
            1,
            substr_count($this->procedureOf($file, 'pipef'), 'pipe_hole_closed')
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
