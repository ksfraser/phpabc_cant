<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\CommentParser;

class CommentParserTest extends TestCase {
    public function testCanParseReturnsTrueForComment() {
        $parser = new CommentParser();
        $this->assertTrue($parser->canParse('% this is a comment'));
    }
    public function testCanParseReturnsFalseForNonComment() {
        $parser = new CommentParser();
        $this->assertFalse($parser->canParse('not a comment'));
    }
    public function testValidateDelegatesToCanParse() {
        $parser = new CommentParser();
        $this->assertTrue($parser->validate('% comment'));
        $this->assertFalse($parser->validate('no comment'));
    }
}
