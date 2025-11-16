<?php

declare(strict_types=1);

namespace Tests\Transform;

use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Transform\VoiceCopyTransform;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;

/**
 * Test suite for VoiceCopyTransform
 * 
 * Tests the Melody-to-Bagpipes voice copying functionality.
 * Business Rule: If Melody voice exists with bars AND Bagpipes voice 
 * does NOT exist OR has no bars, THEN copy all bars from Melody to Bagpipes.
 */
class VoiceCopyTransformTest extends TestCase
{
    private VoiceCopyTransform $transform;

    protected function setUp(): void
    {
        $this->transform = new VoiceCopyTransform();
    }

    // ========================================================================
    // Core Functionality Tests
    // ========================================================================

    /**
     * Test that Melody bars are copied to Bagpipes when no Bagpipes voice exists
     */
    public function testCopyMelodyToBagpipesWhenNoExistingBagpipes(): void
    {
        $abcText = <<<ABC
X:1
T:Test Tune
M:4/4
K:HP
V:M name="Melody" clef=treble
[V:M] A B c d | e f g a |
ABC;

        $tune = AbcTune::parse($abcText);
        $result = $this->transform->transform($tune);

        // Assert Bagpipes voice was created
        $this->assertTrue($result->hasVoice('Bagpipes'), 'Bagpipes voice should be created');

        // Assert Melody voice still exists
        $this->assertTrue($result->hasVoice('M'), 'Melody voice should still exist');

        // Assert bars were copied
        $melodyBars = $result->getBarsForVoice('M');
        $bagpipesBars = $result->getBarsForVoice('Bagpipes');
        
        $this->assertNotNull($melodyBars, 'Melody bars should exist');
        $this->assertNotNull($bagpipesBars, 'Bagpipes bars should exist');
        $this->assertCount(count($melodyBars), $bagpipesBars, 'Bagpipes should have same number of bars as Melody');
    }

    /**
     * Test that no copy occurs when Bagpipes already has bars
     */
    public function testNoCopyWhenBagpipesAlreadyHasBars(): void
    {
        $abcText = <<<ABC
X:1
T:Test Tune
M:4/4
K:HP
V:M name="Melody" clef=treble
[V:M] A B c d |
V:Bagpipes name="Bagpipes" clef=treble
[V:Bagpipes] e f g a |
ABC;

        $tune = AbcTune::parse($abcText);
        $bagpipesBarsBefore = count($tune->getBarsForVoice('Bagpipes') ?? []);
        
        $result = $this->transform->transform($tune);
        
        $bagpipesBarsAfter = count($result->getBarsForVoice('Bagpipes') ?? []);
        
        // Assert bars count didn't change
        $this->assertEquals($bagpipesBarsBefore, $bagpipesBarsAfter, 'Bagpipes bars should not be modified');
    }

    /**
     * Test that no copy occurs when Melody has no bars (header only)
     */
    public function testNoCopyWhenMelodyHasNoBars(): void
    {
        $abcText = <<<ABC
X:1
T:Test Tune
M:4/4
K:HP
V:M name="Melody" clef=treble
ABC;

        $tune = AbcTune::parse($abcText);
        $result = $this->transform->transform($tune);

        // Assert Bagpipes voice was NOT created
        $this->assertFalse($result->hasVoice('Bagpipes'), 'Bagpipes voice should not be created when Melody has no bars');
    }

    /**
     * Test that no copy occurs when Melody voice doesn't exist
     */
    public function testNoCopyWhenMelodyVoiceDoesNotExist(): void
    {
        $abcText = <<<ABC
X:1
T:Test Tune
M:4/4
K:HP
V:Flute name="Flute" clef=treble
[V:Flute] A B c d |
ABC;

        $tune = AbcTune::parse($abcText);
        $result = $this->transform->transform($tune);

        // Assert Bagpipes voice was NOT created
        $this->assertFalse($result->hasVoice('Bagpipes'), 'Bagpipes voice should not be created when no Melody exists');
    }

    // ========================================================================
    // Metadata Tests
    // ========================================================================

    /**
     * Test that copied voice has correct metadata
     */
    public function testCopiedVoiceHasCorrectMetadata(): void
    {
        $abcText = <<<ABC
X:1
T:Test Tune
M:4/4
K:HP
V:M name="Melody" clef=treble
[V:M] A B c d |
ABC;

        $tune = AbcTune::parse($abcText);
        $result = $this->transform->transform($tune);

        $voices = $result->getVoices();
        $this->assertArrayHasKey('Bagpipes', $voices, 'Bagpipes voice should exist in voices array');
        
        $bagpipesVoice = $voices['Bagpipes'];
        $this->assertEquals('Bagpipes', $bagpipesVoice->voiceIndicator ?? $bagpipesVoice->name ?? null, 'Voice indicator should be Bagpipes');
    }

    /**
     * Test that bar order is preserved in copy
     */
    public function testBarOrderPreservedInCopy(): void
    {
        $abcText = <<<ABC
X:1
T:Test Tune
M:4/4
K:HP
V:M name="Melody" clef=treble
[V:M] A B c d | e f g a | B c d e |
ABC;

        $tune = AbcTune::parse($abcText);
        $result = $this->transform->transform($tune);

        $melodyBars = $result->getBarsForVoice('M');
        $bagpipesBars = $result->getBarsForVoice('Bagpipes');

        $this->assertCount(count($melodyBars), $bagpipesBars, 'Bar count should match');
        
        // Check that bar content matches (comparing as strings for simplicity)
        for ($i = 0; $i < count($melodyBars); $i++) {
            $melodyBarContent = $this->getBarContentString($melodyBars[$i]);
            $bagpipesBarContent = $this->getBarContentString($bagpipesBars[$i]);
            
            $this->assertEquals(
                $melodyBarContent, 
                $bagpipesBarContent, 
                "Bar $i content should match"
            );
        }
    }

    /**
     * Test that bar content is preserved in copy
     */
    public function testBarContentPreservedInCopy(): void
    {
        $abcText = <<<ABC
X:1
T:Test Tune with Grace Notes
M:4/4
K:HP
V:M name="Melody" clef=treble
[V:M] {g}A B {d}c d |
ABC;

        $tune = AbcTune::parse($abcText);
        $result = $this->transform->transform($tune);

        $melodyBars = $result->getBarsForVoice('M');
        $bagpipesBars = $result->getBarsForVoice('Bagpipes');

        $this->assertNotEmpty($melodyBars, 'Melody should have bars');
        $this->assertNotEmpty($bagpipesBars, 'Bagpipes should have bars');
    }

    // ========================================================================
    // Edge Cases Tests
    // ========================================================================

    /**
     * Test that multiple bars are all copied
     */
    public function testMultipleMelodyBarsAllCopied(): void
    {
        $abcText = <<<ABC
X:1
T:Test Tune
M:4/4
K:HP
V:M name="Melody" clef=treble
[V:M] A B c d |
[V:M] e f g a |
[V:M] B c d e |
[V:M] f g a b |
ABC;

        $tune = AbcTune::parse($abcText);
        $result = $this->transform->transform($tune);

        $melodyBars = $result->getBarsForVoice('M');
        $bagpipesBars = $result->getBarsForVoice('Bagpipes');

        $this->assertGreaterThanOrEqual(2, count($melodyBars), 'Should have multiple Melody bars');
        $this->assertEquals(count($melodyBars), count($bagpipesBars), 'All bars should be copied');
    }

    /**
     * Test that inline voice markers are handled correctly
     */
    public function testInlineVoiceMarkersCopiedCorrectly(): void
    {
        $abcText = <<<ABC
X:1
T:Test with Inline Markers
M:4/4
K:HP
V:M name="Melody" clef=treble
[V:M] A B c d | e f g a |
ABC;

        $tune = AbcTune::parse($abcText);
        $result = $this->transform->transform($tune);

        $this->assertTrue($result->hasVoice('Bagpipes'), 'Bagpipes voice should be created');
        $this->assertNotEmpty($result->getBarsForVoice('Bagpipes'), 'Bagpipes should have bars');
    }

    /**
     * Test case-insensitive voice matching for Melody
     */
    public function testCaseInsensitiveVoiceMatching(): void
    {
        // Test lowercase 'm'
        $abcText1 = <<<ABC
X:1
T:Test Tune
M:4/4
K:HP
V:m name="Melody" clef=treble
[V:m] A B c d |
ABC;

        $tune1 = AbcTune::parse($abcText1);
        $result1 = $this->transform->transform($tune1);
        $this->assertTrue($result1->hasVoice('Bagpipes'), 'Should recognize lowercase m as Melody');

        // Test "Melody" (full name)
        $abcText2 = <<<ABC
X:1
T:Test Tune
M:4/4
K:HP
V:Melody name="Melody" clef=treble
[V:Melody] A B c d |
ABC;

        $tune2 = AbcTune::parse($abcText2);
        $result2 = $this->transform->transform($tune2);
        $this->assertTrue($result2->hasVoice('Bagpipes'), 'Should recognize "Melody" as melody voice');
    }

    // ========================================================================
    // Voice Variations Tests
    // ========================================================================

    /**
     * Test recognizes "Melody" as a valid melody voice ID
     */
    public function testRecognizesMelodyAsVoiceId(): void
    {
        $abcText = <<<ABC
X:1
T:Test Tune
M:4/4
K:HP
V:Melody name="Melody" clef=treble
[V:Melody] A B c d |
ABC;

        $tune = AbcTune::parse($abcText);
        $result = $this->transform->transform($tune);

        $this->assertTrue($result->hasVoice('Bagpipes'), 'Should create Bagpipes from "Melody" voice');
    }

    /**
     * Test recognizes "M" as a valid melody voice ID
     */
    public function testRecognizesMAsVoiceId(): void
    {
        $abcText = <<<ABC
X:1
T:Test Tune
M:4/4
K:HP
V:M name="Melody" clef=treble
[V:M] A B c d |
ABC;

        $tune = AbcTune::parse($abcText);
        $result = $this->transform->transform($tune);

        $this->assertTrue($result->hasVoice('Bagpipes'), 'Should create Bagpipes from "M" voice');
    }

    /**
     * Test recognizes "Pipes" as existing Bagpipes voice
     */
    public function testRecognizesPipesAsExistingBagpipes(): void
    {
        $abcText = <<<ABC
X:1
T:Test Tune
M:4/4
K:HP
V:M name="Melody" clef=treble
[V:M] A B c d |
V:Pipes name="Pipes" clef=treble
[V:Pipes] e f g a |
ABC;

        $tune = AbcTune::parse($abcText);
        $melodyBarsCount = count($tune->getBarsForVoice('M') ?? []);
        $pipesBarsCount = count($tune->getBarsForVoice('Pipes') ?? []);
        
        $result = $this->transform->transform($tune);
        
        // Pipes should not be modified (already has bars)
        $pipesAfterCount = count($result->getBarsForVoice('Pipes') ?? []);
        $this->assertEquals($pipesBarsCount, $pipesAfterCount, 'Pipes voice should not be modified');
    }

    /**
     * Test recognizes "P" as existing Bagpipes voice
     */
    public function testRecognizesPAsExistingBagpipes(): void
    {
        $abcText = <<<ABC
X:1
T:Test Tune
M:4/4
K:HP
V:M name="Melody" clef=treble
[V:M] A B c d |
V:P name="Pipes" clef=treble
[V:P] e f g a |
ABC;

        $tune = AbcTune::parse($abcText);
        $pBarsCount = count($tune->getBarsForVoice('P') ?? []);
        
        $result = $this->transform->transform($tune);
        
        // P voice should not be modified (already has bars)
        $pAfterCount = count($result->getBarsForVoice('P') ?? []);
        $this->assertEquals($pBarsCount, $pAfterCount, 'P voice should not be modified');
    }

    // ========================================================================
    // Helper Methods
    // ========================================================================

    /**
     * Helper to extract bar content as a string for comparison
     */
    private function getBarContentString($bar): string
    {
        if (method_exists($bar, 'render')) {
            return $bar->render();
        }
        if (method_exists($bar, '__toString')) {
            return (string)$bar;
        }
        if (isset($bar->contentText)) {
            return $bar->contentText;
        }
        // Fallback: try to get notes
        if (isset($bar->notes) && is_array($bar->notes)) {
            return implode(' ', array_map('strval', $bar->notes));
        }
        return '';
    }
}
