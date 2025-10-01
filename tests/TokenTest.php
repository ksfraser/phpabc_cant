<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Token;

class TokenTest extends TestCase {
    public function testConstructorAssignsProperties() {
        $token = new Token('note', 'A', ['octave' => 2]);
        $this->assertEquals('note', $token->type);
        $this->assertEquals('A', $token->value);
        $this->assertEquals(['octave' => 2], $token->meta);
    }
    public function testDefaultMetaIsArray() {
        $token = new Token('note', 'B');
        $this->assertIsArray($token->meta);
        $this->assertEmpty($token->meta);
    }
}
