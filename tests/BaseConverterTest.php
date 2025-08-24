<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\BaseConverter;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Ksfraser\PhpabcCanntaireachd\BaseConverter
 */
class BaseConverterTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new BaseConverter();
        $this->assertInstanceOf(BaseConverter::class, $obj);
    }
}
