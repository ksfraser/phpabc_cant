<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Tokenizer;

class TokenizerTest extends TestCase
{
    public function testTokenize_basicNotes()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize('A B C');
        $this->assertEquals(['A', 'B', 'C'], $tokens);
    }

    public function testTokenize_gracenotes()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize('{g}A {d}B');
        $this->assertEquals(['{g}A', '{d}B'], $tokens);
    }

    public function testTokenize_noteDurations()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize('A3 B2');
        $this->assertEquals(['A3', 'B2'], $tokens);
    }

    public function testTokenize_graceWithDuration()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize('{g}A3 {d}B2');
        $this->assertEquals(['{g}A3', '{d}B2'], $tokens);
    }

    public function testTokenize_mixedBar()
    {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize('{g}A3 B {d}c2 |');
        $this->assertEquals(['{g}A3', 'B', '{d}c2'], $tokens);
    }
}
