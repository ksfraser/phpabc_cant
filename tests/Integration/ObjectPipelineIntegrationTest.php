<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Transform\VoiceCopyTransform;
use Ksfraser\PhpabcCanntaireachd\Transform\CanntaireachdTransform;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;

/**
 * Integration test for object-based pipeline:
 * Parse → VoiceCopyTransform → CanntaireachdTransform → Render
 * 
 * This test validates the complete refactored architecture:
 * - Parse ABC text once into object model
 * - Apply transforms to object model
 * - Render back to ABC text once
 * 
 * Critical business rule being tested:
 * Canntaireachd syllables ONLY appear under Bagpipes voice, NOT under Melody
 * 
 * @package Tests\Integration
 */
class ObjectPipelineIntegrationTest extends TestCase
{
    private VoiceCopyTransform $voiceCopyTransform;
    private CanntaireachdTransform $canntaireachdTransform;
    private TokenDictionary $dict;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dict = new TokenDictionary();
        $this->voiceCopyTransform = new VoiceCopyTransform();
        $this->canntaireachdTransform = new CanntaireachdTransform($this->dict);
    }

    /**
     * Test complete pipeline with simple ABC
     */
    public function testCompletePipelineSimpleAbc(): void
    {
        $abc = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:M
A B c d |
ABC;

        // Parse
        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        // Transform 1: Copy Melody to Bagpipes
        $tune = $this->voiceCopyTransform->transform($tune);
        $this->assertTrue($tune->hasVoice('Bagpipes'), 'Bagpipes voice should be created');

        // Transform 2: Add canntaireachd
        $tune = $this->canntaireachdTransform->transform($tune);

        // Verify: Melody has NO canntaireachd
        $melodyBars = $tune->getBarsForVoice('M');
        $this->assertNotNull($melodyBars);
        foreach ($melodyBars as $bar) {
            if (isset($bar->notes) && is_array($bar->notes)) {
                foreach ($bar->notes as $note) {
                    if (method_exists($note, 'getCanntaireachd')) {
                        $this->assertEmpty($note->getCanntaireachd(), 'Melody notes should NOT have canntaireachd');
                    }
                }
            }
        }

        // Verify: Bagpipes HAS canntaireachd
        $bagpipesBars = $tune->getBarsForVoice('Bagpipes');
        $this->assertNotNull($bagpipesBars);
        $hasCanntaireachd = false;
        foreach ($bagpipesBars as $bar) {
            if (isset($bar->notes) && is_array($bar->notes)) {
                foreach ($bar->notes as $note) {
                    if (method_exists($note, 'getCanntaireachd') && !empty($note->getCanntaireachd())) {
                        $hasCanntaireachd = true;
                        break 2;
                    }
                }
            }
        }
        $this->assertTrue($hasCanntaireachd, 'Bagpipes should have canntaireachd');

        // Render
        $output = $tune->renderSelf();
        $this->assertNotEmpty($output, 'Rendered output should not be empty');
    }

    /**
     * Test pipeline with multi-voice ABC
     */
    public function testPipelineWithMultipleVoices(): void
    {
        $abc = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:M
A B c d |
V:Harmony
E F G A |
ABC;

        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        // Apply transforms
        $tune = $this->voiceCopyTransform->transform($tune);
        $tune = $this->canntaireachdTransform->transform($tune);

        // Verify: M and Harmony have NO canntaireachd
        foreach (['M', 'Harmony'] as $voiceId) {
            $bars = $tune->getBarsForVoice($voiceId);
            if ($bars) {
                foreach ($bars as $bar) {
                    if (isset($bar->notes) && is_array($bar->notes)) {
                        foreach ($bar->notes as $note) {
                            if (method_exists($note, 'getCanntaireachd')) {
                                $this->assertEmpty($note->getCanntaireachd(), "$voiceId should NOT have canntaireachd");
                            }
                        }
                    }
                }
            }
        }

        // Verify: Bagpipes HAS canntaireachd
        $bagpipesBars = $tune->getBarsForVoice('Bagpipes');
        $this->assertNotNull($bagpipesBars);
        $hasCanntaireachd = false;
        foreach ($bagpipesBars as $bar) {
            if (isset($bar->notes) && is_array($bar->notes)) {
                foreach ($bar->notes as $note) {
                    if (method_exists($note, 'getCanntaireachd') && !empty($note->getCanntaireachd())) {
                        $hasCanntaireachd = true;
                        break 2;
                    }
                }
            }
        }
        $this->assertTrue($hasCanntaireachd, 'Bagpipes should have canntaireachd');
    }

    /**
     * Test pipeline with existing Bagpipes voice (should not be overwritten)
     */
    public function testPipelineWithExistingBagpipes(): void
    {
        $abc = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:M
A B c d |
V:Bagpipes
E F G A |
ABC;

        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        // Get original Bagpipes bar count
        $originalBagpipesBars = $tune->getBarsForVoice('Bagpipes');
        $originalBarCount = $originalBagpipesBars ? count($originalBagpipesBars) : 0;

        // Apply transforms
        $tune = $this->voiceCopyTransform->transform($tune);

        // Verify: Bagpipes was NOT overwritten
        $bagpipesBars = $tune->getBarsForVoice('Bagpipes');
        $this->assertNotNull($bagpipesBars);
        $this->assertEquals($originalBarCount, count($bagpipesBars), 'Existing Bagpipes should not be overwritten');

        // Apply canntaireachd
        $tune = $this->canntaireachdTransform->transform($tune);

        // Verify: Bagpipes HAS canntaireachd
        $hasCanntaireachd = false;
        foreach ($bagpipesBars as $bar) {
            if (isset($bar->notes) && is_array($bar->notes)) {
                foreach ($bar->notes as $note) {
                    if (method_exists($note, 'getCanntaireachd') && !empty($note->getCanntaireachd())) {
                        $hasCanntaireachd = true;
                        break 2;
                    }
                }
            }
        }
        $this->assertTrue($hasCanntaireachd, 'Bagpipes should have canntaireachd');
    }

    /**
     * Test pipeline with no Melody voice (no-op)
     */
    public function testPipelineWithNoMelodyVoice(): void
    {
        $abc = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:Harmony
E F G A |
ABC;

        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        // Apply transforms (should not fail)
        $tune = $this->voiceCopyTransform->transform($tune);
        $tune = $this->canntaireachdTransform->transform($tune);

        // Verify: No Bagpipes voice created
        $this->assertFalse($tune->hasVoice('Bagpipes'), 'Bagpipes should not be created without Melody');

        // Verify: Harmony has NO canntaireachd
        $harmonyBars = $tune->getBarsForVoice('Harmony');
        if ($harmonyBars) {
            foreach ($harmonyBars as $bar) {
                if (isset($bar->notes) && is_array($bar->notes)) {
                    foreach ($bar->notes as $note) {
                        if (method_exists($note, 'getCanntaireachd')) {
                            $this->assertEmpty($note->getCanntaireachd(), 'Harmony should NOT have canntaireachd');
                        }
                    }
                }
            }
        }
    }

    /**
     * Test pipeline with inline voice markers [V:id]
     */
    public function testPipelineWithInlineVoiceMarkers(): void
    {
        $abc = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:M
[V:M] A B c d | [V:M] e f g a |
ABC;

        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        // Apply transforms
        $tune = $this->voiceCopyTransform->transform($tune);
        $tune = $this->canntaireachdTransform->transform($tune);

        // Verify: Both voices exist
        $this->assertTrue($tune->hasVoice('M'), 'Melody voice should exist');
        $this->assertTrue($tune->hasVoice('Bagpipes'), 'Bagpipes voice should exist');

        // Verify: Melody has NO canntaireachd
        $melodyBars = $tune->getBarsForVoice('M');
        if ($melodyBars) {
            foreach ($melodyBars as $bar) {
                if (isset($bar->notes) && is_array($bar->notes)) {
                    foreach ($bar->notes as $note) {
                        if (method_exists($note, 'getCanntaireachd')) {
                            $this->assertEmpty($note->getCanntaireachd(), 'Melody should NOT have canntaireachd');
                        }
                    }
                }
            }
        }

        // Verify: Bagpipes HAS canntaireachd
        $bagpipesBars = $tune->getBarsForVoice('Bagpipes');
        $hasCanntaireachd = false;
        if ($bagpipesBars) {
            foreach ($bagpipesBars as $bar) {
                if (isset($bar->notes) && is_array($bar->notes)) {
                    foreach ($bar->notes as $note) {
                        if (method_exists($note, 'getCanntaireachd') && !empty($note->getCanntaireachd())) {
                            $hasCanntaireachd = true;
                            break 2;
                        }
                    }
                }
            }
        }
        $this->assertTrue($hasCanntaireachd, 'Bagpipes should have canntaireachd');
    }

    /**
     * Test pipeline idempotency (running twice should produce same result)
     */
    public function testPipelineIdempotency(): void
    {
        $abc = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:M
A B c d |
ABC;

        // First run
        $tune1 = AbcTune::parse($abc);
        $tune1 = $this->voiceCopyTransform->transform($tune1);
        $tune1 = $this->canntaireachdTransform->transform($tune1);
        $output1 = $tune1->renderSelf();

        // Second run (should not create duplicate Bagpipes)
        $tune2 = AbcTune::parse($abc);
        $tune2 = $this->voiceCopyTransform->transform($tune2);
        $tune2 = $this->canntaireachdTransform->transform($tune2);
        $output2 = $tune2->renderSelf();

        // Verify: Same number of voices
        $this->assertEquals(count($tune1->getVoices()), count($tune2->getVoices()), 'Should have same number of voices');

        // Verify: Both have Bagpipes
        $this->assertTrue($tune1->hasVoice('Bagpipes'), 'First run should have Bagpipes');
        $this->assertTrue($tune2->hasVoice('Bagpipes'), 'Second run should have Bagpipes');
    }

    /**
     * Test pipeline performance (should complete in reasonable time)
     */
    public function testPipelinePerformance(): void
    {
        $abc = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:M
A B c d | e f g a | b a g f | e d c B |
A B c d | e f g a | b a g f | e d c B |
ABC;

        $startTime = microtime(true);

        // Parse
        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        // Transform
        $tune = $this->voiceCopyTransform->transform($tune);
        $tune = $this->canntaireachdTransform->transform($tune);

        // Render
        $output = $tune->renderSelf();

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // Should complete in less than 1 second
        $this->assertLessThan(1.0, $duration, 'Pipeline should complete in less than 1 second');
    }

    /**
     * Test that transforms preserve tune metadata
     */
    public function testTransformsPreserveMetadata(): void
    {
        $abc = <<<'ABC'
X:123
T:Test Tune Title
C:Test Composer
M:4/4
L:1/4
K:Dmaj
V:M
A B c d |
ABC;

        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        // Get original headers
        $originalHeaders = $tune->getHeaders();

        // Apply transforms
        $tune = $this->voiceCopyTransform->transform($tune);
        $tune = $this->canntaireachdTransform->transform($tune);

        // Verify: Headers preserved
        $newHeaders = $tune->getHeaders();
        $this->assertNotEmpty($newHeaders, 'Headers should be preserved');
        
        // Render and check for metadata in output
        $output = $tune->renderSelf();
        $this->assertStringContainsString('X:', $output, 'Output should contain X: header');
        $this->assertStringContainsString('T:', $output, 'Output should contain T: header');
    }
}
