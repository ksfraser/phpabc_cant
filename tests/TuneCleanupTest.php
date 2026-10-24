<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Tests;

use ksfraser\PhpabcCanntaireachd\Cleanup\AbcPitch;
use ksfraser\PhpabcCanntaireachd\Cleanup\BarReflower;
use ksfraser\PhpabcCanntaireachd\Cleanup\DrumVoiceNormaliser;
use ksfraser\PhpabcCanntaireachd\Cleanup\HeaderOrderNormaliser;
use ksfraser\PhpabcCanntaireachd\Cleanup\IncludeBlockWriter;
use ksfraser\PhpabcCanntaireachd\Cleanup\InstrumentProfileRegistry;
use ksfraser\PhpabcCanntaireachd\Cleanup\Parser\AbcDocumentParser;
use ksfraser\PhpabcCanntaireachd\Cleanup\Parser\AbcTokenizer;
use ksfraser\PhpabcCanntaireachd\Cleanup\VoiceAttributeNormaliser;
use ksfraser\PhpabcCanntaireachd\Cleanup\VoiceHeader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ksfraser\PhpabcCanntaireachd\Cleanup\Parser\AbcTokenizer
 * @covers \ksfraser\PhpabcCanntaireachd\Cleanup\Parser\AbcDocumentParser
 * @covers \ksfraser\PhpabcCanntaireachd\Cleanup\HeaderOrderNormaliser
 * @covers \ksfraser\PhpabcCanntaireachd\Cleanup\IncludeBlockWriter
 * @covers \ksfraser\PhpabcCanntaireachd\Cleanup\VoiceAttributeNormaliser
 * @covers \ksfraser\PhpabcCanntaireachd\Cleanup\DrumVoiceNormaliser
 * @covers \ksfraser\PhpabcCanntaireachd\Cleanup\BagpipeScaleChecker
 * @covers \ksfraser\PhpabcCanntaireachd\Cleanup\BarReflower
 * @covers \ksfraser\PhpabcCanntaireachd\Cleanup\AbcPitch
 */
class TuneCleanupTest extends TestCase
{
    // ------------------------------------------------------------------
    // The tokenizer
    // ------------------------------------------------------------------

    public function testTokenizerSplitsNotesAndBarLines(): void
    {
        $items = (new AbcTokenizer())->tokenize('A2B c | D');

        $notes = array_values(array_filter($items, static fn ($i) => $i['type'] === 'note'));
        $bars = array_values(array_filter($items, static fn ($i) => $i['type'] === 'bar'));

        $this->assertCount(4, $notes);
        $this->assertCount(1, $bars);
        $this->assertSame('A', $notes[0]['note']->getPitch());
        $this->assertSame('2', $notes[0]['note']->getLength());
        $this->assertSame('B', $notes[1]['note']->getPitch());
        $this->assertSame('c', $notes[2]['note']->getPitch());
    }

    public function testTokenizerKeepsTheGraceNoteWithItsNote(): void
    {
        $items = (new AbcTokenizer())->tokenize('{g}A2');

        $this->assertCount(1, $items);
        $this->assertSame('{g}A', $items[0]['note']->getDictionaryKey());
    }

    public function testTokenizerKeepsTheSpacingOfTheLine(): void
    {
        $items = (new AbcTokenizer())->tokenize('A  B');

        $this->assertSame('A', $items[0]['note']->render());
        $this->assertSame('  ', $items[1]['text']);
        $this->assertSame('B', $items[2]['note']->render());
    }

    public function testTokenizerReadsEveryBarLine(): void
    {
        $items = (new AbcTokenizer())->tokenize('|] || [: :|');

        $bars = array_values(array_filter($items, static fn ($i) => $i['type'] === 'bar'));
        $this->assertSame(['|]', '||', '[:', ':|'], array_column($bars, 'bar'));
    }

    public function testTokenizerFindsTheLineEndingMarker(): void
    {
        $items = (new AbcTokenizer())->tokenize('A2 $');

        $this->assertSame('lineend', $items[count($items) - 1]['type']);
    }

    // ------------------------------------------------------------------
    // The document parser
    // ------------------------------------------------------------------

    public function testParserReadsTheHeadersAndTheBody(): void
    {
        $abc = "X:1\nT:Test Tune\nK:HP\nM:2/4\nV:Melody name=\"Melody\"\n"
            . "[V:Melody]A2B2 | C2D2 |\nw: en o\n";
        $document = (new AbcDocumentParser())->parse($abc);

        $this->assertCount(1, $document->getTunes());
        $tune = $document->getTunes()[0];
        $this->assertSame('1', $tune->getTuneNumber());
        $this->assertSame('Test Tune', $tune->getTitle());
        $this->assertSame('HP', $tune->getKey());
        $this->assertTrue($tune->isBagpipeTune());
        $this->assertCount(1, $tune->getBodyLines());
        $this->assertSame('Melody', $tune->getBodyLines()[0]->getVoice());
        $this->assertCount(2, $tune->getBodyLines()[0]->getBars());
        $this->assertSame('w: en o', $tune->getBodyLines()[0]->getLyrics()[0]);
    }

    public function testParserKeepsTheHeaderCommentsWithTheHeaders(): void
    {
        $abc = "X:1\nT:Test\n% a note about the tune\nK:HP\nA2 |\n";
        $document = (new AbcDocumentParser())->parse($abc);

        $tune = $document->getTunes()[0];
        $this->assertCount(1, $tune->getHeaderComments());
        $this->assertSame('% a note about the tune', $tune->getHeaderComments()[0]);
    }

    public function testParserTreatsABareVoiceLineInTheBodyAsAVoiceMarker(): void
    {
        $abc = "X:1\nT:Test\nK:HP\nV:Melody\nV:Snare name=\"Snare\"\nA2 |\nV:Snare\nc2 |\n";
        $document = (new AbcDocumentParser())->parse($abc);

        $tune = $document->getTunes()[0];
        // The voice definitions are the ones in the header block.
        $this->assertCount(2, $tune->getHeader('V'));
        // The marker in the body is a body line, as are the two music lines.
        $this->assertCount(3, $tune->getBodyLines());
    }

    // ------------------------------------------------------------------
    // The header order
    // ------------------------------------------------------------------

    public function testHeaderOrderIsThePipeBandOrder(): void
    {
        $order = (new HeaderOrderNormaliser())->getOrder();

        $this->assertSame(
            ['X', 'T', 'C', 'B', 'O', 'Z', 'H', 'Q', 'K', 'M', 'L', 'R', 'U', 'V', 'I'],
            $order
        );
    }

    public function testTheHeadersAreWrittenInOrder(): void
    {
        $abc = "X:1\nT:Test\nK:HP\nM:2/4\nA2 |\n";
        $document = (new AbcDocumentParser())->parse($abc);
        $tune = $document->getTunes()[0];

        $written = [];
        foreach ($tune->render((new HeaderOrderNormaliser())->getOrder()) as $line) {
            if (preg_match('/^([A-Za-z]):/', $line, $matches) === 1) {
                $written[] = $matches[1];
            }
        }

        $this->assertSame(['X', 'T', 'K', 'M'], $written);
    }

    // ------------------------------------------------------------------
    // The include block
    // ------------------------------------------------------------------

    public function testTheIncludeBlockIsWritten(): void
    {
        $writer = new IncludeBlockWriter('/tmp');
        $block = $writer->build('/tmp');

        // Every include is written out: neither abc2midi nor abc2ps follows
        // an include into another include, so a meta-include would leave the
        // tune without its MIDI settings, its fonts and its percussion maps.
        $this->assertCount(10, $block);
        foreach (array_slice($block, 0, -1) as $line) {
            $this->assertStringStartsWith('%%abc-include "', $line);
        }
        $this->assertStringContainsString('format_layout.abh', $block[0]);
        $this->assertStringContainsString('guitar_chords.fmt', $block[5]);
        $this->assertStringContainsString('gchords.fmt', $block[6]);
        $this->assertStringContainsString('jazz.fmt', $block[8]);
        $this->assertStringNotContainsString('common.abh', implode("\n", $block));
    }

    public function testTheIncludeTargetsAreFound(): void
    {
        $writer = new IncludeBlockWriter('/tmp');

        $this->assertSame(
            ['common.abh', 'format_fonts.abh'],
            $writer->extractTargets(['%%abc-include "common.abh"', '% %abc-include "format_fonts.abh"'])
        );
        $this->assertSame(
            ['midi.abh'],
            $writer->extractTargets(['%%abc-include "midi.abh"'])
        );
    }

    public function testAMissingIncludeIsReported(): void
    {
        $writer = new IncludeBlockWriter('/tmp');

        $missing = $writer->findMissing(['%%abc-include "no_such_file.abh"'], '/tmp');

        $this->assertSame(['no_such_file.abh'], $missing);
    }

    // ------------------------------------------------------------------
    // The voice attributes
    // ------------------------------------------------------------------

    public function testAVoiceHeaderIsRead(): void
    {
        $voice = VoiceHeader::parse('V:S gstem=up stem=down stafflines=1 clef=perc middle=G name="Snare" sname="Snare"');

        $this->assertNotNull($voice);
        $this->assertSame('S', $voice->getIndicator());
        $this->assertSame('Snare', $voice->getName());
        $this->assertSame('up', $voice->get('gstem'));
        $this->assertSame('perc', $voice->get('clef'));
        $this->assertSame('1', $voice->get('stafflines'));
    }

    public function testAVoiceGetsTheAttributesItIsMissing(): void
    {
        $normaliser = new VoiceAttributeNormaliser(new InstrumentProfileRegistry());

        $voice = VoiceHeader::parse('V:S');
        $changes = $normaliser->findChanges($voice);

        $this->assertArrayHasKey('name', $changes);
        $this->assertArrayHasKey('clef', $changes);
        $this->assertStringContainsString('name="Snare"', $normaliser->normalise('V:S'));
        $this->assertStringContainsString('clef=perc', $normaliser->normalise('V:S'));
    }

    public function testAVoiceThatIsAlreadyCompleteIsLeftAlone(): void
    {
        $normaliser = new VoiceAttributeNormaliser(new InstrumentProfileRegistry());
        $line = 'V:S gstem=up stem=down stafflines=1 clef=perc middle=G name="Snare" sname="Snare"';
        // Indicator is expanded on output: S -> Snare
        $expected = 'V:Snare gstem=up stem=down stafflines=1 clef=perc middle=G name="Snare" sname="Snare"';

        $this->assertSame([], $normaliser->findChanges(VoiceHeader::parse($line)));
        $this->assertSame($expected, $normaliser->normalise($line));
    }

    public function testAnUnknownVoiceIsLeftAlone(): void
    {
        $normaliser = new VoiceAttributeNormaliser(new InstrumentProfileRegistry());

        $this->assertSame('V:Xylophone', $normaliser->normalise('V:Xylophone'));
    }

    public function testShortIndicatorsAreExpandedToFullNames(): void
    {
        $normaliser = new VoiceAttributeNormaliser(new InstrumentProfileRegistry());

        $this->assertStringContainsString('V:Snare', $normaliser->normalise('V:S'));
        $this->assertStringContainsString('V:Bass', $normaliser->normalise('V:B'));
        $this->assertStringContainsString('V:Tenor', $normaliser->normalise('V:T'));
        $this->assertStringContainsString('V:Melody', $normaliser->normalise('V:M'));
        $this->assertStringContainsString('V:Harmony', $normaliser->normalise('V:H'));
    }

    public function testVSeIsExpandedOnOutput(): void
    {
        $normaliser = new VoiceAttributeNormaliser(new InstrumentProfileRegistry());

        $this->assertStringStartsWith('V:Snare', $normaliser->normalise('V:S'));
        $this->assertStringStartsWith('V:Bass', $normaliser->normalise('V:B'));
        $this->assertStringStartsWith('V:Tenor', $normaliser->normalise('V:T'));
        $this->assertStringStartsWith('V:Melody', $normaliser->normalise('V:M'));
        $this->assertStringStartsWith('V:Harmony', $normaliser->normalise('V:H'));
        $this->assertStringStartsWith('V:Pipes', $normaliser->normalise('V:P'));
    }

    public function testMelodyHasTransposeOne(): void
    {
        $normaliser = new VoiceAttributeNormaliser(new InstrumentProfileRegistry());
        $line = $normaliser->normalise('V:M');

        $this->assertStringContainsString('transpose=1', $line);
    }

    public function testSecondsVoiceIsRegistered(): void
    {
        $registry = new InstrumentProfileRegistry();
        $profile = $registry->find('Seconds');

        $this->assertNotNull($profile);
        $this->assertSame('Seconds', $profile->getName());
        $this->assertSame('Seconds', $profile->getSname());
        $this->assertSame(1, $profile->getTranspose());
    }

    public function testGuitarIsRegistered(): void
    {
        $registry = new InstrumentProfileRegistry();
        $profile = $registry->find('Guitar');

        $this->assertNotNull($profile);
        $this->assertSame('Guitar', $profile->getName());
        $this->assertSame(1, $profile->getOctave());
        $this->assertSame('treble', $profile->getClef());
    }

    public function testBGuitarIsRegistered(): void
    {
        $registry = new InstrumentProfileRegistry();
        $profile = $registry->find('BGuitar');

        $this->assertNotNull($profile);
        $this->assertSame('Bass Guitar', $profile->getName());
        $this->assertSame(1, $profile->getOctave());
        $this->assertSame('bass', $profile->getClef());
    }

    public function testDrumSetIsRegistered(): void
    {
        $registry = new InstrumentProfileRegistry();
        $profile = $registry->find('Drums');

        $this->assertNotNull($profile);
        $this->assertSame('Drums', $profile->getName());
        $this->assertSame(5, $profile->getStafflines());
        $this->assertSame('perc', $profile->getClef());
    }

    // ------------------------------------------------------------------
    // The pipe band drums
    // ------------------------------------------------------------------

    public function testTheDrumNotesAreRecognised(): void
    {
        $drums = new DrumVoiceNormaliser();

        $this->assertSame(['action' => 'ok'], $drums->diagnose(['A', 'c']));
    }

    public function testAPartOnTheWrongNotesIsTransposed(): void
    {
        $drums = new DrumVoiceNormaliser();

        $diagnosis = $drums->diagnose(['d', 'f']);

        $this->assertSame('transpose', $diagnosis['action']);
        $this->assertSame(-5, $diagnosis['transposition']);
    }

    public function testANoteOfTheWrongCaseIsFound(): void
    {
        $drums = new DrumVoiceNormaliser();

        $this->assertSame('case', $drums->diagnose(['A', 'a'])['action']);
        $this->assertSame('case', $drums->diagnose(['A', 'C'])['action']);
    }

    public function testAPartWithOneOddNoteIsATypo(): void
    {
        $drums = new DrumVoiceNormaliser();

        $this->assertSame('typo', $drums->diagnose(['A', 'd'])['action']);
    }

    public function testTheDrumVoicesAreThePipeBandOnes(): void
    {
        $drums = new DrumVoiceNormaliser();

        $this->assertTrue($drums->isPipeBandDrum('S', 'Snare'));
        $this->assertTrue($drums->isPipeBandDrum('B', 'Bass'));
        $this->assertTrue($drums->isPipeBandDrum('T', 'Tenor'));
        $this->assertFalse($drums->isPipeBandDrum('X', 'Drum Set'));
        $this->assertFalse($drums->isPipeBandDrum('X', 'Cymbals'));
        $this->assertFalse($drums->isPipeBandDrum('X', 'Timpani'));
    }

    public function testTheDrumStaffIsOneLine(): void
    {
        $staff = (new DrumVoiceNormaliser())->getPercussionStaff();

        $this->assertSame('1', $staff['stafflines']);
        $this->assertSame('perc', $staff['clef']);
        $this->assertSame('G', $staff['middle']);
    }

    // ------------------------------------------------------------------
    // The pitch
    // ------------------------------------------------------------------

    public function testThePitchIsRead(): void
    {
        $this->assertSame(0, (new AbcPitch('c'))->getSemitone());
        $this->assertSame(-3, (new AbcPitch('A'))->getSemitone());
        $this->assertSame(9, (new AbcPitch('a'))->getSemitone());
        $this->assertSame(2, (new AbcPitch('d'))->getSemitone());
    }

    public function testThePitchIsTransposed(): void
    {
        $this->assertSame('A', (new AbcPitch('d'))->transpose(-5)->render());
        $this->assertSame('c', (new AbcPitch('f'))->transpose(-5)->render());
    }

    // ------------------------------------------------------------------
    // The layout
    // ------------------------------------------------------------------

    public function testTheBarsAreLaidOutFourToALine(): void
    {
        $abc = "X:1\nT:Test\nK:HP\nM:2/4\nL:1/8\nV:Melody\n"
            . "[V:Melody]A2B2 | C2D2 | E2F2 | G2A2 | B2c2 | d2e2 | f2g2 | a2b2 |\n";
        $document = (new AbcDocumentParser())->parse($abc);
        $tune = $document->getTunes()[0];

        $result = (new BarReflower())->reflow($tune);

        $this->assertCount(2, $result['lines']);
        $this->assertCount(4, $result['lines'][0]->getBars());
        $this->assertCount(4, $result['lines'][1]->getBars());
    }

    public function testTheLinesCarryTheVoiceAndTheMeter(): void
    {
        $abc = "X:1\nT:Test\nK:HP\nM:2/4\nL:1/8\nV:Melody\n[V:Melody]A2B2 | C2D2 | E2F2 | G2A2 |\n";
        $document = (new AbcDocumentParser())->parse($abc);
        $tune = $document->getTunes()[0];

        $result = (new BarReflower())->reflow($tune);

        $this->assertStringStartsWith('[V:Melody][M:2/4]', $result['lines'][0]->render());
    }

    public function testTheLinesEndWithTheLineEndingMarker(): void
    {
        $abc = "X:1\nT:Test\nK:HP\nM:2/4\nL:1/8\nV:Melody\n"
            . "[V:Melody]A2B2 | C2D2 | E2F2 | G2A2 | B2c2 |\n";
        $document = (new AbcDocumentParser())->parse($abc);
        $tune = $document->getTunes()[0];

        $result = (new BarReflower())->reflow($tune);

        $this->assertStringEndsWith('$', $result['lines'][0]->render());
    }

    public function testALineWhoseLyricsDoNotMatchItsBarsIsLeftAlone(): void
    {
        $abc = "X:1\nT:Test\nK:HP\nM:2/4\nL:1/8\nV:Melody\n"
            . "[V:Melody]A2B2 | C2D2 | E2F2 | G2A2 | B2c2 |\nw: en o | en o\n";
        $document = (new AbcDocumentParser())->parse($abc);
        $tune = $document->getTunes()[0];

        $result = (new BarReflower())->reflow($tune);

        $this->assertCount(1, $result['lines']);
        $this->assertNotSame([], $result['notes']);
    }
}
