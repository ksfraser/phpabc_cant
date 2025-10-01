<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\BodyLineHandler\LyricsHandler;

class LyricsHandlerTest extends TestCase {
    public function testMatchesReturnsTrueForWLine() {
        $handler = new LyricsHandler(false);
        $this->assertTrue($handler->matches('w:hello'));
    }

    public function testMatchesReturnsFalseForNonWLine() {
        $handler = new LyricsHandler(false);
        $this->assertFalse($handler->matches('not lyrics'));
    }

    public function testHandleSetsLyricsOnBar() {
        $handler = new LyricsHandler(false);
        $context = [
            'currentVoice' => 'V1',
            'currentBar' => 1,
            'voiceBars' => ['V1' => []]
        ];
        $handler->handle($context, 'w:hello world');
        $this->assertEquals('hello world', $context['voiceBars']['V1'][1]->getLyrics());
    }

    public function testHandleWithBarLinesInLyrics() {
        $handler = new LyricsHandler(true);
        $context = [
            'currentVoice' => 'V1',
            'currentBar' => 1,
            'voiceBars' => ['V1' => []]
        ];
        $handler->handle($context, 'w:foo|bar|baz');
        $this->assertEquals('foo', $context['voiceBars']['V1'][1]->getLyrics());
        $this->assertEquals('bar', $context['voiceBars']['V1'][2]->getLyrics());
        $this->assertEquals('baz', $context['voiceBars']['V1'][3]->getLyrics());
        $this->assertEquals(3, $context['currentBar']);
    }
}
