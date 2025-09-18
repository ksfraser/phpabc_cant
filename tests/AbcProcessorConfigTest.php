<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\AbcProcessorConfig;

class AbcProcessorConfigTest extends TestCase {
    public function testDefaults() {
        $config = new AbcProcessorConfig();
        $this->assertEquals('grouped', $config->voiceOutputStyle);
        $this->assertEquals(1, $config->interleaveBars);
        $this->assertEquals(4, $config->barsPerLine);
        $this->assertFalse($config->joinBarsWithBackslash);
    }

    public function testSetVoiceOutputStyle() {
        $config = new AbcProcessorConfig();
        $config->voiceOutputStyle = 'interleaved';
        $this->assertEquals('interleaved', $config->voiceOutputStyle);
    }

    public function testSetInterleaveBars() {
        $config = new AbcProcessorConfig();
        $config->interleaveBars = 3;
        $this->assertEquals(3, $config->interleaveBars);
    }

    public function testSetBarsPerLine() {
        $config = new AbcProcessorConfig();
        $config->barsPerLine = 8;
        $this->assertEquals(8, $config->barsPerLine);
    }

    public function testSetTuneNumberWidth() {
        $config = new AbcProcessorConfig();
        $config->tuneNumberWidth = 3;
        $this->assertEquals(3, $config->tuneNumberWidth);
    }

    public function testCompleteConfiguration() {
        $config = new AbcProcessorConfig();
        $config->voiceOutputStyle = 'interleaved';
        $config->interleaveBars = 2;
        $config->barsPerLine = 6;
        $config->joinBarsWithBackslash = true;
        $config->tuneNumberWidth = 4;

        $this->assertEquals('interleaved', $config->voiceOutputStyle);
        $this->assertEquals(2, $config->interleaveBars);
        $this->assertEquals(6, $config->barsPerLine);
        $this->assertTrue($config->joinBarsWithBackslash);
        $this->assertEquals(4, $config->tuneNumberWidth);
    }

    public function testConfigurationIndependence() {
        $config1 = new AbcProcessorConfig();
        $config2 = new AbcProcessorConfig();

        $config1->voiceOutputStyle = 'interleaved';
        $config1->tuneNumberWidth = 3;

        // config2 should retain defaults
        $this->assertEquals('grouped', $config2->voiceOutputStyle);
        $this->assertEquals(5, $config2->tuneNumberWidth);
    }
