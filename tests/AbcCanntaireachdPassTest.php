<?php
use Ksfraser\PhpabcCanntaireachd\AbcCanntaireachdPass;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;
use PHPUnit\Framework\TestCase;

class AbcCanntaireachdPassTest extends TestCase
{
    private $dict;
    private $pass;

    protected function setUp(): void
    {
        $this->dict = new TokenDictionary();
        // Add some test mappings
        $this->dict->prepopulate([
            'A' => ['cannt_token' => 'dar', 'bmw_token' => null, 'description' => 'Test A'],
            'B' => ['cannt_token' => 'dod', 'bmw_token' => null, 'description' => 'Test B'],
            'C' => ['cannt_token' => 'hid', 'bmw_token' => null, 'description' => 'Test C'],
            'D' => ['cannt_token' => 'dar', 'bmw_token' => null, 'description' => 'Test D']
        ]);
        $this->pass = new AbcCanntaireachdPass($this->dict);
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf(AbcCanntaireachdPass::class, $this->pass);
    }

    public function testProcessEmptyLines()
    {
        $result = $this->pass->process([]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('lines', $result);
        $this->assertArrayHasKey('canntDiff', $result);
        $this->assertEmpty($result['lines']);
        $this->assertIsArray($result['canntDiff']);
    }

    public function testProcessNonBagpipeVoices()
    {
        $lines = [
            'X:1',
            'T:Test Tune',
            'V:Flute',
            'A B C D',
            'V:Drums',
            'A B C D'
        ];

        $result = $this->pass->process($lines);

        // Should not add any w: lines since no Bagpipes voice
        $this->assertCount(6, $result['lines']);
        foreach ($result['lines'] as $line) {
            $this->assertStringStartsNotWith('w:', trim($line));
        }
    }

    public function testProcessBagpipeVoiceWithExistingLyrics()
    {
        $lines = [
            'X:1',
            'T:Test Tune',
            'V:Bagpipes',
            'w: existing lyrics',
            'A B C D'
        ];

        $result = $this->pass->process($lines);

        // Should preserve existing lyrics and not generate new ones
        $this->assertCount(5, $result['lines']);
        $this->assertEquals('w: existing lyrics', $result['lines'][3]);
        $this->assertEquals('A B C D', $result['lines'][4]);
    }

    public function testProcessBagpipeVoiceWithoutLyrics()
    {
        $lines = [
            'X:1',
            'T:Test Tune',
            'V:Bagpipes',
            'A B C D'
        ];

        $result = $this->pass->process($lines);

    // Should add w: line with generated canntaireachd
    $this->assertCount(5, $result['lines']);
    $this->assertEquals('V:Bagpipes', $result['lines'][2]);
    $this->assertEquals('A B C D', $result['lines'][3]);
    $this->assertStringStartsWith('w: ', $result['lines'][4]);
    }

    public function testProcessMultipleVoicesWithBagpipes()
    {
        $lines = [
            'X:1',
            'T:Test Tune',
            'V:Flute',
            'A B C D',
            'V:Bagpipes',
            'A B C D',
            'V:Drums',
            'A B C D'
        ];

        $result = $this->pass->process($lines);

    // Should add w: line only for Bagpipes voice
    $this->assertCount(9, $result['lines']);
    $this->assertEquals('V:Flute', $result['lines'][2]);
    $this->assertEquals('A B C D', $result['lines'][3]);
    $this->assertEquals('V:Bagpipes', $result['lines'][4]);
    $this->assertEquals('A B C D', $result['lines'][5]);
    $this->assertStringStartsWith('w: ', $result['lines'][6]);
    $this->assertEquals('V:Drums', $result['lines'][7]);
    $this->assertEquals('A B C D', $result['lines'][8]);
    }

    public function testProcessBagpipeVoiceWithTimingErrors()
    {
        $lines = [
            'X:1',
            'T:Test Tune',
            'V:Bagpipes',
            'A B C D | A B |TIMING ERROR|'
        ];

        $result = $this->pass->process($lines);

    // Should not add w: line for timing error lines
    $this->assertCount(5, $result['lines']);
    $this->assertEquals('V:Bagpipes', $result['lines'][2]);
    $this->assertEquals('A B C D | A B |TIMING ERROR|', $result['lines'][3]);
    $this->assertStringStartsWith('w: ', $result['lines'][4]);
    }

    public function testProcessCommentsAndEmptyLines()
    {
        $lines = [
            'X:1',
            'T:Test Tune',
            'V:Bagpipes',
            '% This is a comment',
            '',
            'A B C D'
        ];

        $result = $this->pass->process($lines);

    // Should preserve comments and empty lines, add w: line for music
    $this->assertCount(7, $result['lines']);
    $this->assertEquals('V:Bagpipes', $result['lines'][2]);
    $this->assertEquals('% This is a comment', $result['lines'][3]);
    $this->assertEquals('', $result['lines'][4]);
    $this->assertEquals('A B C D', $result['lines'][5]);
    $this->assertStringStartsWith('w: ', $result['lines'][6]);
    }

    public function testProcessMultipleTunes()
    {
        $lines = [
            'X:1',
            'T:First Tune',
            'V:Bagpipes',
            'A B C D',
            '',
            'X:2',
            'T:Second Tune',
            'V:Bagpipes',
            'D C B A'
        ];

        $result = $this->pass->process($lines);

    // Should add w: lines for both tunes
    $this->assertCount(11, $result['lines']);
    // First tune
    $this->assertEquals('A B C D', $result['lines'][4]);
    $this->assertStringStartsWith('w: ', $result['lines'][5]);
    // Second tune
    $this->assertEquals('D C B A', $result['lines'][10]);
    $this->assertStringStartsWith('w: ', $result['lines'][11]);
    }

    public function testCanntaireachdGenerationWithTokenDictionary()
    {
        $lines = [
            'V:Bagpipes',
            'A B C'
        ];

        $result = $this->pass->process($lines);

    // Should generate canntaireachd using token dictionary
    $this->assertCount(3, $result['lines']);
    $this->assertEquals('A B C', $result['lines'][1]);
    $this->assertStringStartsWith('w: ', $result['lines'][2]);
    // Should contain the mapped tokens
    $this->assertStringContainsString('dar', $result['lines'][2]);
    $this->assertStringContainsString('dod', $result['lines'][2]);
    $this->assertStringContainsString('hid', $result['lines'][2]);
    }

    public function testCanntDiffLogging()
    {
        $lines = [
            'V:Bagpipes',
            '%canntaireachd: old text',
            'A B C'
        ];

        $result = $this->pass->process($lines);

        // Should include canntDiff in result
        $this->assertArrayHasKey('canntDiff', $result);
        $this->assertIsArray($result['canntDiff']);
        // The diff should contain information about changes
        $this->assertNotEmpty($result['canntDiff']);
    }

    public function testComplexNotePatterns()
    {
        $lines = [
            'V:Bagpipes',
            'A2 B3 C D/2'
        ];

        $result = $this->pass->process($lines);

    // Should handle complex note patterns with durations
    $this->assertCount(3, $result['lines']);
    $this->assertEquals('A2 B3 C D/2', $result['lines'][1]);
    $this->assertStringStartsWith('w: ', $result['lines'][2]);
    }

    public function testGraceNotesAndEmbellishments()
    {
        $lines = [
            'V:Bagpipes',
            '{g}A B {e}C'
        ];

        $result = $this->pass->process($lines);

    // Should handle grace notes and embellishments
    $this->assertCount(3, $result['lines']);
    $this->assertEquals('{g}A B {e}C', $result['lines'][1]);
    $this->assertStringStartsWith('w: ', $result['lines'][2]);
    }
}
