<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Parser\AccidentalsParser;

class AccidentalsParserTest extends TestCase {
    public function testParseAccidentals() {
        $parser = new AccidentalsParser();
        $result = $parser->parse('^C');
        $this->assertEquals(['^'], $result);
    }
}
