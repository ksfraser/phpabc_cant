<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\AbcEmbellishment;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Ksfraser\PhpabcCanntaireachd\AbcEmbellishment
 */
class AbcEmbellishmentTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new AbcEmbellishment();
        $this->assertInstanceOf(AbcEmbellishment::class, $obj);
    }
}
