<?php
namespace ksfraser\PhpabcCanntaireachd\Tests;
use ksfraser\PhpabcCanntaireachd\BmwFileByToken;
use PHPUnit\Framework\TestCase;
/**
 * @covers \ksfraser\PhpabcCanntaireachd\BmwFileByToken
 */
class BmwFileByTokenTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new BmwFileByToken();
        $this->assertInstanceOf(BmwFileByToken::class, $obj);
    }
}
