<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\BodyLineHandler\SolfegeHandler;

class SolfegeHandlerTest extends TestCase {
    public function testMatchesReturnsTrueForSLine() {
        $handler = new SolfegeHandler();
        $this->assertTrue($handler->matches('S:do re mi'));
    }

    public function testMatchesReturnsFalseForNonSLine() {
        $handler = new SolfegeHandler();
        $this->assertFalse($handler->matches('not solfege'));
    }

    public function testHandleSetsSolfegeOnBar() {
        $handler = new SolfegeHandler();
        $context = [
            'currentVoice' => 'V1',
            'currentBar' => 1,
            'voiceBars' => ['V1' => []]
        ];
        $handler->handle($context, 'S:do re mi');
        $this->assertEquals('do re mi', $context['voiceBars']['V1'][1]->solfege);
    }
}
