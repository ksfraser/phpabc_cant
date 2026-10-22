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

    public function testTheGGraceCanBePlayedOnAnyNoteButTheG(): void
    {
        // A grace sounds the note it is named after, so a G gracenote on a G
        // cannot be heard - whatever the note before it was.
        foreach ($this->fingering->getScaleNotes() as $note) {
            $expected = $note !== 'g';
            $this->assertSame(
                $expected,
                $this->fingering->canPlayGrace('g grace', 'd', $note),
                $note
            );
        }
    }

    public function testTheGracesAndStrikesOfTheOtherLetters(): void
    {
        // {e} is the E gracenote and {E} the E strike, {a} the A gracenote
        // and {A} the A strike.
        $this->assertSame('e', $this->fingering->getGrace('e grace')['note']);
        $this->assertSame('top ring', $this->fingering->getGrace('e grace')['finger']);
        $this->assertSame('A', $this->fingering->getGrace('A grace')['note']);
        $this->assertSame('pinky', $this->fingering->getGrace('A grace')['finger']);
        $this->assertSame('thumb', $this->fingering->getGrace('a grace')['finger']);
    }

    public function testAGraceLowerThanTheNoteItIsPlayedFromIsAStrike(): void
    {
        // A grace whose note is lower than the melody note has to come from a
        // hole below it, and that is a hard movement: a strike.
        $this->assertTrue($this->fingering->isPlayedAsStrikeFrom('g grace', 'a'));
        $this->assertTrue($this->fingering->isPlayedAsStrikeFrom('e grace', 'g'));
        $this->assertTrue($this->fingering->isPlayedAsStrikeFrom('e grace', 'f'));
        $this->assertTrue($this->fingering->isPlayedAsStrikeFrom('A grace', 'e'));
        $this->assertTrue($this->fingering->isPlayedAsStrikeFrom('G grace', 'd'));

        // A grace above the note before and the note after is a gracenote.
        $this->assertFalse($this->fingering->isPlayedAsStrikeFrom('f grace', 'B'));
    }

    public function testTheCaseOfTheLetterSaysWhichNoteItIs(): void
    {
        // {g} is the high G and {G} the low G.
        $this->assertSame('g grace', $this->fingering->getGraceName('g'));
        $this->assertSame('G grace', $this->fingering->getGraceName('G'));
        $this->assertSame('a grace', $this->fingering->getGraceName('a'));
        $this->assertSame('A grace', $this->fingering->getGraceName('A'));

        $this->assertTrue($this->fingering->isLowNote('G'));
        $this->assertFalse($this->fingering->isLowNote('g'));
    }

    public function testTheAGraceOnTheLowGIsAGracenote(): void
    {
        // G{A}G would be an A grace on the low G. It is physically possible,
        // but nobody writes it.
        $this->assertFalse($this->fingering->isPlayedAsStrikeFrom('A grace', 'G'));
        $this->assertTrue($this->fingering->canPlayGrace('A grace', 'G', 'G'));
    }

    public function testTheGGraceCannotBePlayedOnTheHighG(): void
    {
        // The note is already a G there, and there is nothing to hear.
        $this->assertFalse($this->fingering->canPlayGrace('g grace', 'g', 'g'));
        $this->assertFalse($this->fingering->canPlayGrace('g grace', 'd', 'g'));
    }

    public function testTheDGraceCannotBePlayedOnAD(): void
    {
        // The false D sounds the D, so it cannot be heard on a D.
        $this->assertFalse($this->fingering->canPlayGrace('d grace', 'c', 'd'));
        $this->assertTrue($this->fingering->canPlayGrace('d grace', 'c', 'c'));
    }

    public function testTheLowGGraceCanBePlayedFromEveryNote(): void
    {
        foreach ($this->fingering->getScaleNotes() as $note) {
            $this->assertTrue($this->fingering->canPlayGrace('G grace', $note), $note);
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

    public function testTheGGraceHasNoFingeringWhereItIsNotInTheScale(): void
    {
        $this->assertNull($this->fingering->getGraceFingering('g grace', 'x'));
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
        // The high G, then a G gracenote on a G: the note is already a G, and
        // there is nothing to hear.
        $body = new AbcBodyLine([], 'M', '2/4', 'HP');
        $body->setBars([new AbcBarModel([['note', new AbcNoteModel('g')]])]);
        $next = new AbcBodyLine([], 'M', '2/4', 'HP');
        $next->setBars([new AbcBarModel([['note', new AbcNoteModel('g', '', '', '{g}')]])]);

        $problems = $checker->check([$body, $next]);

        $this->assertSame(
            ['a g grace on a g cannot be heard: it sounds the note it is on'],
            $problems
        );
    }

    public function testTheGraceCheckerIsQuietWhenTheGraceIsLifted(): void
    {
        $checker = new BagpipeGraceChecker();
        $body = new AbcBodyLine([], 'M', '2/4', 'HP');
        $body->setBars([new AbcBarModel([['note', new AbcNoteModel('d')]])]);
        $next = new AbcBodyLine([], 'M', '2/4', 'HP');
        $next->setBars([new AbcBarModel([['note', new AbcNoteModel('A', '', '', '{g}')]])]);

        $this->assertSame([], $checker->check([$body, $next]));
    }

    public function testAGraceGroupIsASequenceOfGraces(): void
    {
        // {GdGeAfA}e is the low G, the D gracenote, the high G, the E
        // gracenote, the low A, the F gracenote and the low A.
        $this->assertSame(
            ['G', 'd', 'G', 'e', 'A', 'f', 'A'],
            $this->fingering->getGraceLetters('GdGeAfA')
        );
    }

    public function testTheCommonGracesAndTheNotesTheEmbellishmentsAreMadeOf(): void
    {
        $this->assertSame(['g', 'd', 'e'], BagpipeFingering::COMMON_GRACES);
        $this->assertSame(['a', 'g', 'f', 'e', 'd'], BagpipeFingering::EMBELLISHMENT_NOTES);
    }

    public function testEachGraceOfAGroupIsChecked(): void
    {
        $checker = new BagpipeGraceChecker();
        // The high G, then a grace group whose last letter is a low G on a low
        // G: that one cannot be heard.
        $body = new AbcBodyLine([], 'M', '2/4', 'HP');
        $body->setBars([new AbcBarModel([['note', new AbcNoteModel('g')]])]);
        $next = new AbcBodyLine([], 'M', '2/4', 'HP');
        $next->setBars([new AbcBarModel([['note', new AbcNoteModel('G', '', '', '{GdG}')]])]);

        $problems = $checker->check([$body, $next]);

        $this->assertContains(
            'a G grace on a G cannot be heard: it sounds the note it is on',
            $problems
        );
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
