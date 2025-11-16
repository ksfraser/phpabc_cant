<?php

declare(strict_types=1);

namespace Tests\Transform;

use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Transform\CanntaireachdTransform;
use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;

/**
 * Test suite for CanntaireachdTransform
 * 
 * Tests the business rule: Canntaireachd syllables ONLY added to Bagpipes voices, NOT Melody
 * 
 * @package Tests\Transform
 */
class CanntaireachdTransformTest extends TestCase
{
    private CanntaireachdTransform $transform;
    private TokenDictionary $dict;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dict = new TokenDictionary();
        $this->transform = new CanntaireachdTransform($this->dict);
    }

    /**
     * Test that canntaireachd is added to Bagpipes voice
     */
    public function testAddsCanntaireachdToBagpipesVoice(): void
    {
        $abc = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:Bagpipes
A B c d |
ABC;

        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        $result = $this->transform->transform($tune);
        
        // Verify Bagpipes voice still exists
        $this->assertTrue($result->hasVoice('Bagpipes'), 'Bagpipes voice should exist');
        
        // Get bars and verify canntaireachd was added
        $bars = $result->getBarsForVoice('Bagpipes');
        $this->assertNotNull($bars, 'Bagpipes voice should have bars');
        $this->assertCount(1, $bars, 'Should have 1 bar');
        
        // Check that notes have canntaireachd
        $bar = $bars[0];
        $this->assertNotEmpty($bar->notes, 'Bar should have notes');
        
        // At least one note should have canntaireachd
        $hasCanntaireachd = false;
        foreach ($bar->notes as $note) {
            if (method_exists($note, 'getCanntaireachd') && !empty($note->getCanntaireachd())) {
                $hasCanntaireachd = true;
                break;
            }
        }
        $this->assertTrue($hasCanntaireachd, 'At least one note should have canntaireachd');
    }

    /**
     * Test that canntaireachd is NOT added to Melody voice
     */
    public function testDoesNotAddCanntaireachdToMelodyVoice(): void
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

        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        $result = $this->transform->transform($tune);
        
        // Verify Melody voice still exists
        $this->assertTrue($result->hasVoice('M'), 'Melody voice should exist');
        
        // Get bars and verify NO canntaireachd was added
        $bars = $result->getBarsForVoice('M');
        $this->assertNotNull($bars, 'Melody voice should have bars');
        
        // Check that notes do NOT have canntaireachd
        foreach ($bars as $bar) {
            if (isset($bar->notes) && is_array($bar->notes)) {
                foreach ($bar->notes as $note) {
                    if (method_exists($note, 'getCanntaireachd')) {
                        $cannt = $note->getCanntaireachd();
                        $this->assertEmpty($cannt, 'Melody notes should NOT have canntaireachd');
                    }
                }
            }
        }
    }

    /**
     * Test that canntaireachd is added to "Pipes" voice (alias)
     */
    public function testAddsCanntaireachdToPipesVoice(): void
    {
        $abc = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:Pipes
A B c d |
ABC;

        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        $result = $this->transform->transform($tune);
        
        // Verify Pipes voice still exists
        $this->assertTrue($result->hasVoice('Pipes'), 'Pipes voice should exist');
        
        // Get bars and verify canntaireachd was added
        $bars = $result->getBarsForVoice('Pipes');
        $this->assertNotNull($bars, 'Pipes voice should have bars');
        
        // Check that notes have canntaireachd
        $hasCanntaireachd = false;
        foreach ($bars as $bar) {
            if (isset($bar->notes) && is_array($bar->notes)) {
                foreach ($bar->notes as $note) {
                    if (method_exists($note, 'getCanntaireachd') && !empty($note->getCanntaireachd())) {
                        $hasCanntaireachd = true;
                        break 2;
                    }
                }
            }
        }
        $this->assertTrue($hasCanntaireachd, 'At least one note should have canntaireachd');
    }

    /**
     * Test that canntaireachd is added to "P" voice (short form)
     */
    public function testAddsCanntaireachdToPVoice(): void
    {
        $abc = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:P
A B c d |
ABC;

        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        $result = $this->transform->transform($tune);
        
        // Verify P voice still exists
        $this->assertTrue($result->hasVoice('P'), 'P voice should exist');
        
        // Get bars and verify canntaireachd was added
        $bars = $result->getBarsForVoice('P');
        $this->assertNotNull($bars, 'P voice should have bars');
        
        // Check that notes have canntaireachd
        $hasCanntaireachd = false;
        foreach ($bars as $bar) {
            if (isset($bar->notes) && is_array($bar->notes)) {
                foreach ($bar->notes as $note) {
                    if (method_exists($note, 'getCanntaireachd') && !empty($note->getCanntaireachd())) {
                        $hasCanntaireachd = true;
                        break 2;
                    }
                }
            }
        }
        $this->assertTrue($hasCanntaireachd, 'At least one note should have canntaireachd');
    }

    /**
     * Test case-insensitive voice matching
     */
    public function testCaseInsensitiveVoiceMatching(): void
    {
        $abc = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:bagpipes
A B c d |
ABC;

        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        $result = $this->transform->transform($tune);
        
        // Verify bagpipes voice exists (lowercase)
        $this->assertTrue($result->hasVoice('bagpipes'), 'bagpipes voice should exist');
        
        // Get bars and verify canntaireachd was added
        $bars = $result->getBarsForVoice('bagpipes');
        $this->assertNotNull($bars, 'bagpipes voice should have bars');
        
        // Check that notes have canntaireachd
        $hasCanntaireachd = false;
        foreach ($bars as $bar) {
            if (isset($bar->notes) && is_array($bar->notes)) {
                foreach ($bar->notes as $note) {
                    if (method_exists($note, 'getCanntaireachd') && !empty($note->getCanntaireachd())) {
                        $hasCanntaireachd = true;
                        break 2;
                    }
                }
            }
        }
        $this->assertTrue($hasCanntaireachd, 'At least one note should have canntaireachd');
    }

    /**
     * Test multi-voice tune with both Melody and Bagpipes
     */
    public function testMultiVoiceTuneWithMelodyAndBagpipes(): void
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
A B c d |
ABC;

        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        $result = $this->transform->transform($tune);
        
        // Verify both voices exist
        $this->assertTrue($result->hasVoice('M'), 'Melody voice should exist');
        $this->assertTrue($result->hasVoice('Bagpipes'), 'Bagpipes voice should exist');
        
        // Check Melody has NO canntaireachd
        $melodyBars = $result->getBarsForVoice('M');
        $this->assertNotNull($melodyBars, 'Melody voice should have bars');
        foreach ($melodyBars as $bar) {
            if (isset($bar->notes) && is_array($bar->notes)) {
                foreach ($bar->notes as $note) {
                    if (method_exists($note, 'getCanntaireachd')) {
                        $cannt = $note->getCanntaireachd();
                        $this->assertEmpty($cannt, 'Melody notes should NOT have canntaireachd');
                    }
                }
            }
        }
        
        // Check Bagpipes HAS canntaireachd
        $bagpipesBars = $result->getBarsForVoice('Bagpipes');
        $this->assertNotNull($bagpipesBars, 'Bagpipes voice should have bars');
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
     * Test tune with no Bagpipes voice (no-op)
     */
    public function testTuneWithNoBagpipesVoice(): void
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

        // Should not throw exception
        $result = $this->transform->transform($tune);
        
        // Both voices should still exist unchanged
        $this->assertTrue($result->hasVoice('M'), 'Melody voice should exist');
        $this->assertTrue($result->hasVoice('Harmony'), 'Harmony voice should exist');
    }

    /**
     * Test tune with empty bars
     */
    public function testTuneWithEmptyBars(): void
    {
        $abc = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:Bagpipes
ABC;

        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        // Should not throw exception
        $result = $this->transform->transform($tune);
        
        $this->assertTrue($result->hasVoice('Bagpipes'), 'Bagpipes voice should exist');
    }

    /**
     * Test multiple bars in Bagpipes voice
     */
    public function testMultipleBarsInBagpipesVoice(): void
    {
        $abc = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:Bagpipes
A B c d | e f g a | b a g f |
ABC;

        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        $result = $this->transform->transform($tune);
        
        $bars = $result->getBarsForVoice('Bagpipes');
        $this->assertNotNull($bars, 'Bagpipes voice should have bars');
        $this->assertGreaterThanOrEqual(1, count($bars), 'Should have at least 1 bar');
        
        // Each bar with notes should get canntaireachd
        foreach ($bars as $bar) {
            if (isset($bar->notes) && is_array($bar->notes) && count($bar->notes) > 0) {
                $hasCanntaireachd = false;
                foreach ($bar->notes as $note) {
                    if (method_exists($note, 'getCanntaireachd') && !empty($note->getCanntaireachd())) {
                        $hasCanntaireachd = true;
                        break;
                    }
                }
                $this->assertTrue($hasCanntaireachd, 'Bar with notes should have canntaireachd');
            }
        }
    }

    /**
     * Test that transform returns the same tune object (immutability/mutability)
     */
    public function testTransformReturnsSameTuneObject(): void
    {
        $abc = <<<'ABC'
X:1
T:Test Tune
M:4/4
L:1/4
K:Dmaj
V:Bagpipes
A B c d |
ABC;

        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        $result = $this->transform->transform($tune);
        
        // Should return the same object (modified in place)
        $this->assertSame($tune, $result, 'Transform should return the same tune object');
    }

    /**
     * Test with real-world test-Suo.abc file structure
     */
    public function testRealWorldTuneStructure(): void
    {
        $abc = <<<'ABC'
X:1
T:Suo Ghan (Black Chanter)
C:Traditional (19thC)
M:C
L:1/8
K:Dmix
V:M
GABc dedB | cBcd edef | 
V:Bagpipes
GABc dedB | cBcd edef |
ABC;

        $tune = AbcTune::parse($abc);
        $this->assertNotNull($tune, 'Failed to parse ABC');

        $result = $this->transform->transform($tune);
        
        // Melody should have NO canntaireachd
        $melodyBars = $result->getBarsForVoice('M');
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
        
        // Bagpipes should have canntaireachd
        $bagpipesBars = $result->getBarsForVoice('Bagpipes');
        $this->assertNotNull($bagpipesBars, 'Bagpipes voice should have bars');
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
}
