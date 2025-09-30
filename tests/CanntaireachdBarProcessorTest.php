<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\CanntaireachdBarProcessor;

class CanntaireachdBarProcessorTest extends TestCase
{
    public function testTokensToCanntaireachd_basicMapping()
    {
        $dict = [
            'A' => 'dah',
            'B' => 'em',
            'C' => 'ho',
        ];
        $processor = new CanntaireachdBarProcessor($dict);
        $tokens = ['A', 'B', 'C'];
        $result = $processor->tokensToCanntaireachd($tokens);
        $this->assertEquals('dah em ho', $result);
    }

    public function testTokensToCanntaireachd_skipsBarlines()
    {
        $dict = ['A' => 'dah'];
        $processor = new CanntaireachdBarProcessor($dict);
        $tokens = ['|', 'A', '||', 'A', '|:', ':'];
        $result = $processor->tokensToCanntaireachd($tokens);
        $this->assertEquals('dah dah', $result);
    }

    public function testTokensToCanntaireachd_fallbackToToken()
    {
        $dict = ['A' => 'dah'];
        $processor = new CanntaireachdBarProcessor($dict);
        $tokens = ['A', 'X', 'B'];
        $result = $processor->tokensToCanntaireachd($tokens);
        $this->assertEquals('dah X B', $result);
    }
}
