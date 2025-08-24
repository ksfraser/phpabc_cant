<?php
namespace Ksfraser\PhpabcCanntaireachd\Tests;
use Ksfraser\PhpabcCanntaireachd\AbcVoice;
use PHPUnit\Framework\TestCase;
/**
 * @covers \Ksfraser\PhpabcCanntaireachd\AbcVoice
 */
class AbcVoiceTest extends TestCase {
    public function testCanInstantiate() {
        $obj = new AbcVoice();
        $this->assertInstanceOf(AbcVoice::class, $obj);
    }
}
