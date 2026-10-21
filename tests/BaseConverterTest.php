<?php
namespace ksfraser\PhpabcCanntaireachd\Tests;
use ksfraser\PhpabcCanntaireachd\BaseConverter;
use PHPUnit\Framework\TestCase;
/**
 * @covers \ksfraser\PhpabcCanntaireachd\BaseConverter
 */
class BaseConverterTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new BaseConverter();
        $this->assertInstanceOf(BaseConverter::class, $obj);
    }
}
