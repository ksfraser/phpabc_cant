<?php
namespace ksfraser\PhpabcCanntaireachd\Tests;
use ksfraser\PhpabcCanntaireachd\AbcEmbellishment;
use PHPUnit\Framework\TestCase;
/**
 * @covers \ksfraser\PhpabcCanntaireachd\AbcEmbellishment
 */
class AbcEmbellishmentTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new AbcEmbellishment();
        $this->assertInstanceOf(AbcEmbellishment::class, $obj);
    }
}
