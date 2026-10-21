<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Tests;

use ksfraser\PhpabcCanntaireachd\AbcCanntaireachdPass;
use ksfraser\PhpabcCanntaireachd\TokenDictionary;
use ksfraser\PhpabcCanntaireachd\Voices\TargetVoiceResolver;
use PHPUnit\Framework\TestCase;

/**
 * The rule under test:
 *
 *   A voice is a target when its identifier or its declared name is Melody,
 *   Bagpipes or Pipes (case-insensitively). Every music line of every target
 *   voice is converted to canntaireachd and the result is written as a "w:"
 *   line directly beneath the ABC line it belongs to.
 *
 * @covers \ksfraser\PhpabcCanntaireachd\AbcCanntaireachdPass
 * @covers \ksfraser\PhpabcCanntaireachd\Voices\TargetVoiceResolver
 */
class AbcVoiceCanntaireachdPassTest extends TestCase
{
    private AbcCanntaireachdPass $pass;

    protected function setUp(): void
    {
        $this->pass = new AbcCanntaireachdPass(new TokenDictionary());
    }

    /**
     * @param string[] $lines
     * @return string[]
     */
    private function processLines(array $lines): array
    {
        return $this->pass->process($lines)['lines'];
    }

    public function testMelodyVoiceGetsCanntaireachd(): void
    {
        $result = $this->processLines([
            'V:Melody',
            'A B',
        ]);

        $this->assertSame([
            'V:Melody',
            'A B',
            'w: en o',
        ], $result);
    }

    public function testBagpipesVoiceGetsCanntaireachd(): void
    {
        $result = $this->processLines([
            'V:Bagpipes',
            'A B',
        ]);

        $this->assertSame('w: en o', $result[2]);
    }

    public function testPipesVoiceGetsCanntaireachd(): void
    {
        $result = $this->processLines([
            'V:Pipes',
            'A B',
        ]);

        $this->assertSame('w: en o', $result[2]);
    }

    public function testVoiceIsMatchedByNameAttribute(): void
    {
        $result = $this->processLines([
            'V:M name="Melody" sname="Melody"',
            'A B',
        ]);

        $this->assertSame('w: en o', $result[2]);
    }

    public function testVoiceMatchingIsCaseInsensitive(): void
    {
        $result = $this->processLines([
            'V:BAGPIPES',
            'A B',
            'V:x name="MeLoDy"',
            'A B',
        ]);

        $this->assertSame('w: en o', $result[2]);
        $this->assertSame('w: en o', $result[5]);
    }

    public function testNonTargetVoiceIsUntouched(): void
    {
        $result = $this->processLines([
            'V:Flute',
            'A B',
            'V:Drums',
            'A B',
        ]);

        $this->assertSame([
            'V:Flute',
            'A B',
            'V:Drums',
            'A B',
        ], $result);
    }

    public function testEveryLineOfTheVoiceIsProcessed(): void
    {
        $result = $this->processLines([
            'V:Melody',
            'A B',
            'C D',
            'E F',
        ]);

        $this->assertSame([
            'V:Melody',
            'A B',
            'w: en o',
            'C D',
            'w: [C] [D]',
            'E F',
            // E and F are outside the bagpipe range, so they have no syllable.
            'w: [E] [F]',
        ], $result);
    }

    public function testInlineVoiceMarkerSwitchesVoice(): void
    {
        $result = $this->processLines([
            '[V:Flute] A B',
            '[V:Bagpipes] A B',
        ]);

        $this->assertSame([
            '[V:Flute] A B',
            '[V:Bagpipes] A B',
            'w: en o',
        ], $result);
    }

    public function testInlineVoiceMarkerIsMatchedByNameDeclaredLater(): void
    {
        // The name is declared after the body lines it describes.
        $result = $this->processLines([
            '[V:M] A B',
            'V:M name="Melody"',
        ]);

        $this->assertSame([
            '[V:M] A B',
            'w: en o',
            'V:M name="Melody"',
        ], $result);
    }

    public function testExistingWLineIsKeptAndGeneratedOneIsAddedAfterIt(): void
    {
        $result = $this->processLines([
            'V:Bagpipes',
            'A B',
            'w: hand written',
        ]);

        $this->assertSame([
            'V:Bagpipes',
            'A B',
            'w: hand written',
            'w: en o',
        ], $result);
    }

    public function testHeadersCommentsAndDirectivesAreNotMusic(): void
    {
        $result = $this->processLines([
            'X:1',
            'T:Title',
            'K:HP',
            '% a comment',
            '%%MIDI program 109 #bagpipes',
            'V:Bagpipes',
            'A B',
        ]);

        $this->assertSame([
            'X:1',
            'T:Title',
            'K:HP',
            '% a comment',
            '%%MIDI program 109 #bagpipes',
            'V:Bagpipes',
            'A B',
            'w: en o',
        ], $result);
    }

    public function testOriginalLinesArePreservedVerbatim(): void
    {
        $lines = [
            'X:1',
            'V:Bagpipes',
            '[V:Bagpipes]{g}A3B {g}ce3 | {g}B3A {g}c{d}B3 |',
        ];

        $result = $this->processLines($lines);

        $this->assertSame($lines[0], $result[0]);
        $this->assertSame($lines[1], $result[1]);
        $this->assertSame($lines[2], $result[2]);
        $this->assertSame('w: hen o ho e | ho en ho do |', $result[3]);
    }

    public function testCanntaireachdIsWrittenBeneathTheAbcLine(): void
    {
        $result = $this->processLines([
            'V:Bagpipes',
            'A B',
            'C D',
        ]);

        // The w: line must come directly after the line it describes.
        $this->assertSame('A B', $result[1]);
        $this->assertSame('w: en o', $result[2]);
        $this->assertSame('C D', $result[3]);
        $this->assertSame('w: [C] [D]', $result[4]);
    }

    public function testNewTuneResetsTheVoice(): void
    {
        $result = $this->processLines([
            'X:1',
            'V:Bagpipes',
            'A B',
            'X:2',
            'A B',
        ]);

        // The second tune has no voice header, so its music is not attributed
        // to the Bagpipes voice of the first tune.
        $this->assertSame([
            'X:1',
            'V:Bagpipes',
            'A B',
            'w: en o',
            'X:2',
            'A B',
        ], $result);
    }

    public function testCanntDiffRecordsWhatWasGenerated(): void
    {
        $result = $this->pass->process([
            'V:Bagpipes',
            'A B',
        ]);

        $this->assertCount(1, $result['canntDiff']);
        $this->assertSame('A B', $result['canntDiff'][0]['line']);
        $this->assertSame('en o', $result['canntDiff'][0]['generated']);
    }

    public function testTargetVoicesCanBeOverridden(): void
    {
        $pass = new AbcCanntaireachdPass(new TokenDictionary(), ['Chanter']);
        $result = $pass->process([
            'V:Chanter',
            'A B',
            'V:Bagpipes',
            'A B',
        ])['lines'];

        $this->assertSame('w: en o', $result[2]);
        // Bagpipes is no longer a target, so its line is left alone.
        $this->assertSame([
            'V:Chanter',
            'A B',
            'w: en o',
            'V:Bagpipes',
            'A B',
        ], $result);
    }

    public function testGraceNotesKeepTheirPrefixInTheDictionaryLookup(): void
    {
        $result = $this->processLines([
            'V:Bagpipes',
            '{g}A3B',
        ]);

        // "{g}A" is one dictionary key (hen), not "g" followed by "A".
        $this->assertSame('w: hen o', $result[2]);
    }

    public function testResolverMatchesIdentifierOrName(): void
    {
        $resolver = new TargetVoiceResolver();

        $this->assertTrue($resolver->isTarget('Melody'));
        $this->assertTrue($resolver->isTarget('BAGPIPES'));
        $this->assertTrue($resolver->isTarget('M', 'Melody'));
        $this->assertTrue($resolver->isTarget('M', 'Bagpipes'));
        $this->assertFalse($resolver->isTarget('Flute'));
        $this->assertFalse($resolver->isTarget('M', 'Flute'));
        $this->assertFalse($resolver->isTarget(null));
    }

    public function testResolverExtractsTheDeclaredName(): void
    {
        $this->assertSame(
            'Melody',
            TargetVoiceResolver::extractName(' name="Melody" sname="Melody"')
        );
        $this->assertNull(TargetVoiceResolver::extractName(' sname="Melody"'));
    }

    public function testResolverCollectsVoiceNames(): void
    {
        $resolver = new TargetVoiceResolver();

        $names = $resolver->collectVoiceNames([
            'V:M name="Melody" sname="Melody"',
            'V:H name="Harmony"',
            'V:Flute',
        ]);

        $this->assertSame(['M' => 'Melody', 'H' => 'Harmony'], $names);
    }
}
