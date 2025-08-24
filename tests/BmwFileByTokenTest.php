<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\BmwFileByToken;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Ksfraser\PhpabcCanntaireachd\BmwFileByToken
 */
class BmwFileByTokenTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new BmwFileByToken();
        $this->assertInstanceOf(BmwFileByToken::class, $obj);
    }
}
