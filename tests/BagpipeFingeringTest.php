<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Tests;

use ksfraser\PhpabcCanntaireachd\Bagpipe\BagpipeFingering;
use ksfraser\PhpabcCanntaireachd\Bagpipe\BagpipeGraceChecker;
use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcBarModel;
use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcBodyLine;
use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcNoteModel;
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

    public function testTheGGraceIsPlayedByLiftingTheTopIndex(): void
    {
        $grace = $this->fingering->getGrace('g grace');

        $this->assertNotNull($grace);
        $this->assertSame('top index', $grace['finger']);
        $this->assertSame('open', $grace['action']);
        $this->assertSame('g', $grace['note']);
    }

    public function testTheGGraceCannotBePlayedFromTheHighAOrTheHighG(): void
    {
        // On those notes the top index hole is already open, so there is
        // nothing to lift.
        $this->assertFalse($this->fingering->canPlayGrace('g grace', 'a'));
        $this->assertFalse($this->fingering->canPlayGrace('g grace', 'g'));

        foreach (['G', 'A', 'B', 'c', 'd', 'e', 'f'] as $note) {
            $this->assertTrue($this->fingering->canPlayGrace('g grace', $note), $note);
        }
    }

    public function testTheFingeringOfTheGGraceIsTheNoteBeforeWithTheTopIndexOpen(): void
    {
        // From the D: the top index finger is up, and the little finger is
        // down as well, because the D is played with it.
        $this->assertSame(
            ['thumb', 'top middle', 'top ring', 'pinky'],
            $this->fingering->getGraceFingering('g grace', 'd')
        );

        // From the B: the top index finger up, the rest as they are.
        $this->assertSame(
            ['thumb', 'top middle', 'top ring', 'bottom index', 'bottom middle'],
            $this->fingering->getGraceFingering('g grace', 'B')
        );
    }

    public function testTheGGraceHasNoFingeringWhereItCannotBePlayed(): void
    {
        $this->assertNull($this->fingering->getGraceFingering('g grace', 'a'));
    }

    public function testTheDoublingIsTheGGraceThenTheFalseD(): void
    {
        $steps = $this->fingering->getSteps('doubling');

        $this->assertCount(4, $steps);
        $this->assertSame('top index', $steps[0]['finger']);
        $this->assertSame('g', $steps[0]['note']);
        $this->assertNull($steps[1]['finger']);
        $this->assertSame('bottom index', $steps[2]['finger']);
        $this->assertSame('d', $steps[2]['note']);
    }

    public function testTheDThrowBeatsOnTheRightIndexFinger(): void
    {
        $steps = $this->fingering->getSteps('D throw');

        // Close to the G, open to the false D, close to the C, open to the
        // melody note: the right index finger does all of it.
        $this->assertSame(
            ['bottom index', 'bottom index', 'bottom index', 'bottom index'],
            array_column($steps, 'finger')
        );
        $this->assertSame(['G', 'd', 'c', 'melody'], array_column($steps, 'note'));
    }

    public function testTheGripClosesToTheGAndBack(): void
    {
        $steps = $this->fingering->getSteps('grip');

        $this->assertSame(['G', 'd', 'G', 'melody'], array_column($steps, 'note'));
        $this->assertNull($steps[3]['finger']);
    }

    public function testTheGraceCheckerFindsAGraceThatCannotBePlayed(): void
    {
        $checker = new BagpipeGraceChecker();
        $lines = [
            // The high A, then a G gracenote: the top index hole is already
            // open on the high A, so there is nothing to lift.
            new AbcBodyLine([], 'M', '2/4', 'HP'),
        ];
        $body = new AbcBodyLine([], 'M', '2/4', 'HP');
        $body->setBars([new AbcBarModel([['note', new AbcNoteModel('a')]])]);
        $next = new AbcBodyLine([], 'M', '2/4', 'HP');
        $next->setBars([new AbcBarModel([['note', new AbcNoteModel('A', '', '', '{g}')]])]);

        $problems = $checker->check([$body, $next]);

        $this->assertSame(['the g grace cannot be played from the a'], $problems);
    }

    public function testTheGraceCheckerIsQuietWhenTheGraceCanBePlayed(): void
    {
        $checker = new BagpipeGraceChecker();
        $body = new AbcBodyLine([], 'M', '2/4', 'HP');
        $body->setBars([new AbcBarModel([['note', new AbcNoteModel('d')]])]);
        $next = new AbcBodyLine([], 'M', '2/4', 'HP');
        $next->setBars([new AbcBarModel([['note', new AbcNoteModel('A', '', '', '{g}')]])]);

        $this->assertSame([], $checker->check([$body, $next]));
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
