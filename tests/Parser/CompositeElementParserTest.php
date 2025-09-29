<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Parser\CompositeElementParser;
use Ksfraser\PhpabcCanntaireachd\NoteElement\CompositeElement;
class CompositeElementParserTest extends TestCase {
    public function testParseCompositeElement() {
        // Create mock element classes with getName()
        $foo = new class {
            public function getName() { return 'foo'; }
        };
        $bar = new class {
            public function getName() { return 'bar'; }
        };
        $composite = new CompositeElement();
        $composite->addElement($foo);
        $composite->addElement($bar);
        $parser = new CompositeElementParser($composite);
        $result = $parser->parse('foo bar');
        $this->assertArrayHasKey('foo', $result);
        $this->assertArrayHasKey('bar', $result);
        $this->assertNull($parser->parse('baz'));
    }
}
