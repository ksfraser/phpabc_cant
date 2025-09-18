<?php
use Ksfraser\PhpabcCanntaireachd\ParseContext;
use PHPUnit\Framework\TestCase;

class ParseContextTest extends TestCase
{
    private $voiceBars;
    private $context;

    protected function setUp(): void
    {
        $this->voiceBars = [];
        $this->context = new ParseContext($this->voiceBars);
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf(ParseContext::class, $this->context);
        $this->assertInstanceOf(\ArrayAccess::class, $this->context);
    }

    public function testInitialState()
    {
        $this->assertNull($this->context->currentVoice);
        $this->assertEquals(0, $this->context->currentBar);
        $this->assertIsArray($this->context->voiceBars);
        $this->assertEmpty($this->context->voiceBars);
    }

    public function testGetOrCreateVoiceCreatesNewVoice()
    {
        $voiceBars = $this->context->getOrCreateVoice('Bagpipes');

        $this->assertIsArray($voiceBars);
        $this->assertEmpty($voiceBars);
        $this->assertEquals('Bagpipes', $this->context->currentVoice);
        $this->assertArrayHasKey('Bagpipes', $this->voiceBars);
        $this->assertIsArray($this->voiceBars['Bagpipes']);
    }

    public function testGetOrCreateVoiceReturnsExistingVoice()
    {
        // Create voice first time
        $this->voiceBars['Bagpipes'] = ['bar1', 'bar2'];
        $voiceBars = $this->context->getOrCreateVoice('Bagpipes');

        $this->assertIsArray($voiceBars);
        $this->assertCount(2, $voiceBars);
        $this->assertEquals(['bar1', 'bar2'], $voiceBars);
        $this->assertEquals('Bagpipes', $this->context->currentVoice);
    }

    public function testIncrementBar()
    {
        $this->assertEquals(0, $this->context->currentBar);

        $newBar = $this->context->incrementBar();
        $this->assertEquals(1, $newBar);
        $this->assertEquals(1, $this->context->currentBar);

        $newBar = $this->context->incrementBar();
        $this->assertEquals(2, $newBar);
        $this->assertEquals(2, $this->context->currentBar);
    }

    public function testArrayAccessOffsetExists()
    {
        $this->assertTrue($this->context->offsetExists('currentVoice'));
        $this->assertTrue($this->context->offsetExists('currentBar'));
        $this->assertTrue($this->context->offsetExists('voiceBars'));
        $this->assertFalse($this->context->offsetExists('nonexistent'));
    }

    public function testArrayAccessOffsetGet()
    {
        $this->assertNull($this->context->offsetGet('currentVoice'));
        $this->assertEquals(0, $this->context->offsetGet('currentBar'));
        $this->assertIsArray($this->context->offsetGet('voiceBars'));
        $this->assertNull($this->context->offsetGet('nonexistent'));
    }

    public function testArrayAccessOffsetSet()
    {
        $this->context->offsetSet('currentVoice', 'Drums');
        $this->assertEquals('Drums', $this->context->currentVoice);

        $this->context->offsetSet('currentBar', 5);
        $this->assertEquals(5, $this->context->currentBar);

        $testArray = ['test' => 'value'];
        $this->context->offsetSet('voiceBars', $testArray);
        $this->assertEquals($testArray, $this->context->voiceBars);
    }

    public function testArrayAccessOffsetUnset()
    {
        $this->context->currentVoice = 'Bagpipes';
        $this->context->currentBar = 3;

        $this->context->offsetUnset('currentVoice');
        $this->assertFalse(isset($this->context->currentVoice));

        $this->context->offsetUnset('currentBar');
        $this->assertFalse(isset($this->context->currentBar));
    }

    public function testVoiceBarsReference()
    {
        // Test that voiceBars is a reference to the original array
        $this->voiceBars['TestVoice'] = ['bar1'];
        $this->assertArrayHasKey('TestVoice', $this->voiceBars);

        // Modify through context
        $this->context->voiceBars['TestVoice'][] = 'bar2';
        $this->assertCount(2, $this->voiceBars['TestVoice']);
        $this->assertEquals(['bar1', 'bar2'], $this->voiceBars['TestVoice']);
    }

    public function testMultipleVoiceManagement()
    {
        $voice1Bars = $this->context->getOrCreateVoice('Bagpipes');
        $this->assertEquals('Bagpipes', $this->context->currentVoice);

        $voice2Bars = $this->context->getOrCreateVoice('Drums');
        $this->assertEquals('Drums', $this->context->currentVoice);

        // Both voices should exist
        $this->assertArrayHasKey('Bagpipes', $this->voiceBars);
        $this->assertArrayHasKey('Drums', $this->voiceBars);

        // Switching back should work
        $voice1BarsAgain = $this->context->getOrCreateVoice('Bagpipes');
        $this->assertEquals('Bagpipes', $this->context->currentVoice);
        $this->assertSame($voice1Bars, $voice1BarsAgain);
    }

    public function testBarIncrementIndependence()
    {
        // Bar increment should be independent of voice switching
        $this->context->incrementBar();
        $this->assertEquals(1, $this->context->currentBar);

        $this->context->getOrCreateVoice('Bagpipes');
        $this->assertEquals(1, $this->context->currentBar);

        $this->context->incrementBar();
        $this->assertEquals(2, $this->context->currentBar);
    }

    public function testArrayAccessCompatibility()
    {
        // Test that array access works like regular array operations
        $this->context['currentVoice'] = 'Flute';
        $this->assertEquals('Flute', $this->context['currentVoice']);

        $this->context['currentBar'] = 10;
        $this->assertEquals(10, $this->context['currentBar']);

        $this->assertTrue(isset($this->context['voiceBars']));
        unset($this->context['currentVoice']);
        $this->assertFalse(isset($this->context['currentVoice']));
    }
}
