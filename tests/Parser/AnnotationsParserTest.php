<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Parser\AnnotationsParser;

class AnnotationsParserTest extends TestCase {
    public function testParseAnnotations() {
        $parser = new AnnotationsParser();
        $result = $parser->parse('!trill!C');
        $this->assertEquals(['!trill!'], $result);
    }
}
