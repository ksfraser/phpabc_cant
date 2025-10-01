<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\BodyLineHandler\BarLineHandler;

class BarLineHandlerTest extends TestCase {
    public function testMatchesReturnsTrueForValidBarLine() {
        $handler = new BarLineHandler(['|', '||', '|:', ':|']);
        $this->assertTrue($handler->matches('|: some notes'));
    }

    public function testMatchesReturnsFalseForInvalidBarLine() {
        $handler = new BarLineHandler(['|', '||', '|:', ':|']);
        $this->assertFalse($handler->matches('not a barline'));
    }

    public function testHandleIncrementsBarAndCreatesBarObject() {
        $handler = new BarLineHandler(['|', '||', '|:', ':|']);
        $context = (object)[
            'currentBar' => 0,
            'currentVoice' => 'V1',
            'voiceBars' => ['V1' => []]
        ];
        $handler->handle($context, '|:');
        $this->assertEquals(1, $context->currentBar);
        $this->assertArrayHasKey(1, $context->voiceBars['V1']);
    }
}
