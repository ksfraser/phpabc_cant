<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Decorator\DecoratorLoader;

class DecoratorLoaderTest extends TestCase {
    public function testDecoratorMapIsPopulated() {
        $map = DecoratorLoader::getDecoratorMap();
        $this->assertIsArray($map);
        $this->assertNotEmpty($map, 'Decorator map should not be empty');
        // Check for some known shortcuts
        $this->assertArrayHasKey('.', $map, 'Staccato shortcut should be present');
        $this->assertArrayHasKey('!staccato!', $map, 'Staccato bang shortcut should be present');
        $this->assertArrayHasKey('!fermata!', $map, 'Fermata shortcut should be present');
        $this->assertArrayHasKey('tr', $map, 'Trill shortcut should be present');
        // Check that the mapped value is a class name
        $this->assertTrue(class_exists($map['.']), 'StaccatoDecorator class should exist');
    }
}
