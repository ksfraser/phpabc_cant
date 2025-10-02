<?php
use Ksfraser\PhpabcCanntaireachd\AbcCommentLine;
use PHPUnit\Framework\TestCase;

class AbcCommentLineTest extends TestCase {
    public function testStripsLeadingPercentAndWhitespace() {
        $comment = new AbcCommentLine('%  This is a comment');
        $this->assertEquals("This is a comment", $this->getCommentProperty($comment));
    }

    public function testStripsMultiplePercents() {
        $comment = new AbcCommentLine('%%Extra');
        $this->assertEquals("Extra", $this->getCommentProperty($comment));
    }

    public function testNoPercent() {
        $comment = new AbcCommentLine('No percent');
        $this->assertEquals("No percent", $this->getCommentProperty($comment));
    }

    public function testRenderSelfOutputsPercentPrefix() {
        $comment = new AbcCommentLine('Rendered');
        $rendered = $this->invokeRenderSelf($comment);
        $this->assertEquals("%Rendered\n", $rendered);
    }

    private function getCommentProperty($obj) {
        $ref = new ReflectionClass($obj);
        $prop = $ref->getProperty('comment');
        $prop->setAccessible(true);
        return $prop->getValue($obj);
    }

    private function invokeRenderSelf($obj) {
        $ref = new ReflectionClass($obj);
        $method = $ref->getMethod('renderSelf');
        $method->setAccessible(true);
        return $method->invoke($obj);
    }
}
