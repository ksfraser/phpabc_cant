<?php
use PHPUnit\Framework\TestCase;
use Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderV;

class AbcHeaderVoiceTest extends TestCase {
    public function testLabelIsV() {
        $this->assertEquals('V', AbcHeaderV::$label);
    }
    public function testSettersAssignProperties() {
        $v = new AbcHeaderV();
        $v->setName('Main');
        $v->setSname('Sub');
        $v->setStem('up');
        $v->setGstem('down');
        $v->setClef('treble');
        $v->setTranspose(2);
        $v->setMiddle('B');
        $v->setOctave(1);
        $v->setStafflines(5);
        $this->assertTrue(true); // If no exception, setters work
    }
}
