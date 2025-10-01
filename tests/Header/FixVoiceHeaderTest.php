<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../src/Ksfraser/PhpabcCanntaireachd/Header/FixVoiceHeader.php';

class FixVoiceHeaderTest extends TestCase {
    public function testFixHeaderAddsMissingNameAndSname() {
        $lineObj = $this->getMockBuilder(stdClass::class)
            ->addMethods(['render', 'setHeaderLine'])
            ->getMock();
        $lineObj->expects($this->once())
            ->method('render')
            ->willReturn('V:V1');
        $lineObj->expects($this->once())
            ->method('setHeaderLine')
            ->with($this->stringContains('name="V1"'));
    $log = \Ksfraser\PhpabcCanntaireachd\Header\FixVoiceHeader::fixHeader($lineObj);
        $this->assertStringContainsString('Applied name="V1"', $log);
        $this->assertStringContainsString('Applied sname="V1"', $log);
    }
}
