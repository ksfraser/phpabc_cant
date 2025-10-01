<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\BodyLineHandler\CanntaireachdHandler;

class CanntaireachdHandlerTest extends TestCase {
    public function testMatchesReturnsTrueForWLine() {
        $handler = new CanntaireachdHandler();
        $this->assertTrue($handler->matches('W:hello'));
    }

    public function testMatchesReturnsFalseForNonWLine() {
        $handler = new CanntaireachdHandler();
        $this->assertFalse($handler->matches('not canntaireachd'));
    }

    public function testHandleSetsCanntaireachdOnBar() {
        $handler = new CanntaireachdHandler();
        $context = [
            'currentVoice' => 'V1',
            'currentBar' => 1,
            'voiceBars' => ['V1' => []]
        ];
        $handler->handle($context, 'W:hello');
        $this->assertEquals('hello', $context['voiceBars']['V1'][1]->getCanntaireachd());
    }
}
