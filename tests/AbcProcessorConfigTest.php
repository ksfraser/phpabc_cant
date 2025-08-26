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

    public function testSetJoinBarsWithBackslash() {
        $config = new AbcProcessorConfig();
        $config->joinBarsWithBackslash = true;
        $this->assertTrue($config->joinBarsWithBackslash);
    }
}
