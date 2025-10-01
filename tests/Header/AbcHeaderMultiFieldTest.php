<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderMultiField;

class AbcHeaderMultiFieldTest extends TestCase {
    public function testSetAndGetStoresMultipleValues() {
        $mock = $this->getMockForAbstractClass(AbcHeaderMultiField::class);
        $mock->set('foo');
        $mock->set('bar');
        $this->assertEquals(['foo', 'bar'], $mock->get());
    }

    public function testRenderOutputsAllValues() {
        $mock = $this->getMockForAbstractClass(AbcHeaderMultiField::class);
        $mock::$label = 'C';
        $mock->set('composer1');
        $mock->set('composer2');
        $output = $mock->render();
        $this->assertStringContainsString('C:composer1', $output);
        $this->assertStringContainsString('C:composer2', $output);
    }
}
