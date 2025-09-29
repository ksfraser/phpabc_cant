<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Parser\TypesettingSpaceParser;
class TypesettingSpaceParserTest extends TestCase {
    public function testParseTypesettingSpace() {
        $parser = new TypesettingSpaceParser();
        $this->assertEquals('y', $parser->parse('Cy')); // Should find 'y'
        $this->assertNull($parser->parse('C')); // Should not find 'y'
    }
}
