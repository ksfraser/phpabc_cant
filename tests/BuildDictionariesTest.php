<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\BuildDictionaries;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Ksfraser\PhpabcCanntaireachd\BuildDictionaries
 */
class BuildDictionariesTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new BuildDictionaries();
        $this->assertInstanceOf(BuildDictionaries::class, $obj);
    }
}
