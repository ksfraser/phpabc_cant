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
        $tokens = ['A', 'B', 'C'];
        $result = \Ksfraser\PhpabcCanntaireachd\CanntaireachdBarProcessor::tokensToCanntaireachd($tokens, $dict);
        $this->assertEquals('dah em ho', $result);
    }

    public function testTokensToCanntaireachd_skipsBarlines()
    {
        $dict = ['A' => 'dah'];
        $tokens = ['|', 'A', '||', 'A', '|:', ':'];
        $result = \Ksfraser\PhpabcCanntaireachd\CanntaireachdBarProcessor::tokensToCanntaireachd($tokens, $dict);
        $this->assertEquals('dah dah', $result);
    }

    public function testTokensToCanntaireachd_fallbackToToken()
    {
        $dict = ['A' => 'dah'];
        $tokens = ['A', 'X', 'B'];
        $result = \Ksfraser\PhpabcCanntaireachd\CanntaireachdBarProcessor::tokensToCanntaireachd($tokens, $dict);
        $this->assertEquals('dah X B', $result);
    }
}
