<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderGeneric;

class AbcHeaderGenericTest extends TestCase {
    public function testAddAndGet() {
        $header = new AbcHeaderGeneric();
        $header->setLabel('X');
        $header->add('foo');
        $header->add('bar');
        $this->assertEquals('foo, bar', $header->get());
    }
    public function testSetArray() {
        $header = new AbcHeaderGeneric();
        $header->setLabel('Y');
        $header->set(['a', 'b']);
        $this->assertEquals('a, b', $header->get());
    }
    public function testRenderOutputsAllValues() {
        $header = new AbcHeaderGeneric();
        $header->setLabel('Z');
        $header->add('val1');
        $header->add('val2');
        $output = $header->render();
        $this->assertStringContainsString('Z:val1', $output);
        $this->assertStringContainsString('Z:val2', $output);
    }
}
