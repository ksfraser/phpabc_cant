<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\AbcFile;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Ksfraser\PhpabcCanntaireachd\AbcFile
 */
class AbcFileTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new AbcFile();
        $this->assertInstanceOf(AbcFile::class, $obj);
    }
}
