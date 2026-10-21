<?php
namespace ksfraser\PhpabcCanntaireachd\Tests;
use ksfraser\PhpabcCanntaireachd\BuildDictionaries;
use PHPUnit\Framework\TestCase;
/**
 * @covers \ksfraser\PhpabcCanntaireachd\BuildDictionaries
 */
class BuildDictionariesTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new BuildDictionaries();
        $this->assertInstanceOf(BuildDictionaries::class, $obj);
    }
}
